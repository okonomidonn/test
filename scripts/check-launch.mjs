import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
require('@next/env').loadEnvConfig(process.cwd());

// 値や接続文字列は出さず、不足項目だけを表示する。
const missing = [];
const required = ['AUTH_SECRET', 'DATABASE_URL', 'DATABASE_URL_UNPOOLED', 'APP_URL', 'CRON_SECRET', 'OPENAI_API_KEY', 'GEMINI_API_KEY', 'LEGAL_SELLER_NAME', 'LEGAL_REPRESENTATIVE', 'LEGAL_ADDRESS', 'LEGAL_PHONE', 'LEGAL_EMAIL', 'LEGAL_PAYMENT_METHODS', 'LEGAL_PAYMENT_TIMING', 'LEGAL_CANCELLATION_TERMS'];
for (const name of required) if (!process.env[name]?.trim()) missing.push(name);
if (process.env.AUTH_SECRET && process.env.AUTH_SECRET.length < 32) missing.push('AUTH_SECRET（32文字以上）');
for (const name of ['APP_URL', 'DATABASE_URL', 'DATABASE_URL_UNPOOLED']) {
  if (!process.env[name]) continue;
  try {
    const url = new URL(process.env[name]);
    if (['localhost', '127.0.0.1', 'host'].includes(url.hostname)) missing.push(`${name}（本番用の接続先）`);
    if (name === 'APP_URL' && url.protocol !== 'https:') missing.push('APP_URL（HTTPS）');
    if (name === 'DATABASE_URL_UNPOOLED' && url.hostname.includes('-pooler.')) missing.push('DATABASE_URL_UNPOOLED（プーリングなし）');
  } catch { missing.push(`${name}（URL形式）`); }
}
if (process.env.AI_SIGNUP_OPEN === '1') missing.push('AI_SIGNUP_OPEN（セルフ版登録を停止）');
if (process.env.GEO_ENGINES?.split(',').includes('mock')) missing.push('GEO_ENGINES（本番ではmockを使わない）');
console.log('有料の代行受付に向けた設定確認');
console.log(missing.length ? `未完了：${[...new Set(missing)].join('、')}` : '必須の環境変数は設定されています。');
const mailReady = ['RESEND_API_KEY', 'MAIL_FROM', 'LEAD_NOTIFY_EMAIL'].every((key) => !!process.env[key]);
console.log(process.env.LEAD_NOTIFICATIONS_ENABLED === '1' && mailReady ? '運営通知：有効（実際の受信確認は別途必要）' : '運営通知：停止中または未設定。一覧を毎営業日確認してください。');
if (process.env.STRIPE_SECRET_KEY) console.log(process.env.STRIPE_SECRET_KEY.startsWith('sk_live_') ? '既存セルフ版Stripe：本番モードのキー形式' : '既存セルフ版Stripe：テストモードまたは不明なキー形式');
console.log('手動確認：Vercelの商用利用可能なプラン、Preview用DB分離、送信元ドメインの確認、契約書・見積書、実AI計測、担当者の返信体制。Stripeの設定確認は既存セルフ版契約がある場合に行います。');
process.exitCode = missing.length ? 1 : 0;
