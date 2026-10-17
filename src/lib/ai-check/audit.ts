import { lookup } from "node:dns/promises";
import { isIP } from "node:net";

// 店舗の公式サイトを読み、AIに伝わりにくい点を洗い出す。
// URLは利用者が入力するため、社内ネットワーク等へのアクセスに使われないよう宛先を検査する。

export type CheckStatus = "ok" | "warn" | "ng";
export type AuditCheck = { key: string; title: string; status: CheckStatus; detail: string; fix?: string };

const MAX_BYTES = 2_000_000;
const AI_CRAWLERS = ["GPTBot", "OAI-SearchBot", "ChatGPT-User", "Google-Extended", "ClaudeBot", "PerplexityBot"];

function isPrivateAddress(ip: string): boolean {
  if (isIP(ip) === 6) {
    const v = ip.toLowerCase();
    if (v === "::1" || v === "::" || v.startsWith("fc") || v.startsWith("fd") || v.startsWith("fe80")) return true;
    const mapped = v.match(/^::ffff:(\d+\.\d+\.\d+\.\d+)$/);
    return mapped ? isPrivateAddress(mapped[1]) : false;
  }
  const [a, b] = ip.split(".").map(Number);
  return (
    a === 0 || a === 10 || a === 127 || (a === 100 && b >= 64 && b <= 127) || (a === 169 && b === 254) ||
    (a === 172 && b >= 16 && b <= 31) || (a === 192 && b === 168) || (a === 198 && (b === 18 || b === 19)) || a >= 224
  );
}

async function assertPublicUrl(url: URL) {
  if (url.protocol !== "https:" && url.protocol !== "http:") throw new Error("http(s) のURLを指定してください");
  if (url.username || url.password) throw new Error("URLにユーザー名やパスワードは含められません");
  const host = url.hostname.replace(/^\[|\]$/g, "");
  const addresses = isIP(host) ? [host] : (await lookup(host, { all: true })).map((a) => a.address);
  if (addresses.length === 0 || addresses.some(isPrivateAddress)) throw new Error("このURLにはアクセスできません");
}

async function safeFetch(raw: string): Promise<{ url: string; status: number; body: string }> {
  let url = new URL(raw);
  for (let hop = 0; hop < 4; hop++) {
    await assertPublicUrl(url);
    const res = await fetch(url, {
      redirect: "manual",
      signal: AbortSignal.timeout(12_000),
      headers: { "User-Agent": "Mozilla/5.0 (compatible; AI-Search-Check/1.0)", Accept: "text/html,*/*" },
    });
    const location = res.headers.get("location");
    if (res.status >= 300 && res.status < 400 && location) {
      url = new URL(location, url);
      continue;
    }
    const reader = res.body?.getReader();
    const chunks: Uint8Array[] = [];
    let size = 0;
    while (reader) {
      const { done, value } = await reader.read();
      if (done) break;
      size += value.byteLength;
      if (size > MAX_BYTES) {
        await reader.cancel();
        break;
      }
      chunks.push(value);
    }
    return { url: url.toString(), status: res.status, body: Buffer.concat(chunks).toString("utf8") };
  }
  throw new Error("リダイレクトが多すぎます");
}

function visibleText(html: string): string {
  return html
    .replace(/<!--[\s\S]*?-->/g, " ")
    .replace(/<(script|style|noscript|template)[^>]*>[\s\S]*?<\/\1>/gi, " ")
    .replace(/<[^>]+>/g, " ")
    .replace(/&nbsp;/g, " ")
    .replace(/&amp;/g, "&")
    .replace(/\s+/g, " ")
    .trim();
}

function jsonLdTypes(html: string): string[] {
  const types: string[] = [];
  for (const m of html.matchAll(/<script[^>]*application\/ld\+json[^>]*>([\s\S]*?)<\/script>/gi)) {
    for (const t of m[1].matchAll(/"@type"\s*:\s*"([^"]+)"/g)) types.push(t[1]);
  }
  return types;
}

function blockedCrawlers(robots: string): string[] {
  const blocked: string[] = [];
  let agents: string[] = [];
  let inRules = false;
  for (const line of robots.split(/\r?\n/)) {
    const [rawKey, ...rest] = line.split("#")[0].split(":");
    const key = rawKey?.trim().toLowerCase();
    const value = rest.join(":").trim();
    if (key === "user-agent") {
      if (inRules) agents = [];
      inRules = false;
      agents.push(value);
    } else if (key === "disallow" || key === "allow") {
      inRules = true;
      if (key === "disallow" && value === "/") {
        for (const bot of AI_CRAWLERS) {
          if (agents.some((a) => a === "*" || a.toLowerCase() === bot.toLowerCase())) blocked.push(bot);
        }
      }
    }
  }
  return [...new Set(blocked)];
}

const SCENES: { key: string; label: string; words: RegExp; example: string }[] = [
  { key: "solo", label: "一人飲み", words: /一人|ひとり|お一人様|おひとり|1人/, example: "カウンター席があり、お一人様でもお気軽にどうぞ。" },
  { key: "date", label: "デート", words: /デート|カップル|記念日|お二人/, example: "落ち着いた店内で、デートや記念日にもご利用いただけます。" },
  { key: "group", label: "少人数・宴会", words: /少人数|宴会|貸切|貸し切り|団体|飲み会|女子会/, example: "4名様のテーブル席や、○名様までの貸切もご相談ください。" },
  { key: "budget", label: "予算の目安", words: /予算[^。]{0,15}?[0-9０-９][0-9０-９,，]*\s*円|お一人様\s*[0-9０-９][0-9０-９,，]*\s*円|[0-9０-９][0-9０-９,，]{3,}\s*円\s*[〜~～]/, example: "ご予算の目安はお一人様3,000〜4,000円です。" },
];

export async function auditSite(input: { websiteUrl: string; area: string; genre: string }) {
  const checks: AuditCheck[] = [];
  let page: Awaited<ReturnType<typeof safeFetch>>;
  try {
    page = await safeFetch(input.websiteUrl);
  } catch (e) {
    checks.push({
      key: "fetch",
      title: "公式サイトを読み込めるか",
      status: "ng",
      detail: `読み込めませんでした（${e instanceof Error ? e.message : String(e)}）`,
      fix: "URLが正しいか確認してください。AIもこのサイトを読めていない可能性があります。",
    });
    return { url: input.websiteUrl, score: 0, checks };
  }

  const html = page.body;
  const text = visibleText(html);
  const title = html.match(/<title[^>]*>([\s\S]*?)<\/title>/i)?.[1]?.trim() ?? "";
  const description = html.match(/<meta[^>]+name=["']description["'][^>]*content=["']([^"']*)["']/i)?.[1] ?? "";
  const genreWord = input.genre.split(/[・、,／/ ]/)[0] ?? input.genre;

  checks.push({
    key: "fetch",
    title: "公式サイトを読み込めるか",
    status: page.status < 400 ? "ok" : "ng",
    detail: page.status < 400 ? `読み込めました（${page.url}）` : `エラー（HTTP ${page.status}）`,
  });

  const titleHasArea = (title + description).includes(input.area);
  const titleHasGenre = (title + description).includes(genreWord);
  checks.push({
    key: "title",
    title: "ページの題名・説明にエリアと業態があるか",
    status: titleHasArea && titleHasGenre ? "ok" : "warn",
    detail: `題名：「${title || "なし"}」`,
    fix: titleHasArea && titleHasGenre ? undefined : `題名か説明文に「${input.area}」「${genreWord}」を入れると、AIが何の店かを判断しやすくなります。`,
  });

  checks.push({
    key: "text",
    title: "AIが読める文章の量",
    status: text.length >= 800 ? "ok" : text.length >= 300 ? "warn" : "ng",
    detail: `ページ内の文章は約${text.length.toLocaleString()}文字`,
    fix: text.length >= 800 ? undefined : "文章が少ないか、画像やプログラムで表示されているため、AIに内容が伝わりにくい状態です。お店の特徴を文章で書き足しましょう。",
  });

  const prices = (text.match(/[0-9０-９][0-9０-９,，]{1,6}\s*円/g) ?? []).length;
  checks.push({
    key: "menu",
    title: "メニューと価格が文字で書かれているか",
    status: prices >= 5 ? "ok" : prices > 0 ? "warn" : "ng",
    detail: `価格の記載 ${prices}件`,
    fix: prices >= 5 ? undefined : "メニューが画像やプログラムで表示されていると、AIは料理名や価格を読めません。主な料理と価格を文字で載せましょう。",
  });

  const hasAddress = /[都道府県].{0,8}[市区町村]|〒\s*\d{3}-?\d{4}/.test(text);
  const hasPhone = /0\d{1,4}-\d{1,4}-\d{3,4}/.test(text);
  const hasHours = /営業時間|[0-2]?\d[:：]\d{2}\s*[-〜~～]/.test(text);
  const hasHoliday = /定休日|休業日|定休/.test(text);
  const missing = [!hasAddress && "住所", !hasPhone && "電話番号", !hasHours && "営業時間", !hasHoliday && "定休日"].filter(Boolean);
  checks.push({
    key: "nap",
    title: "住所・電話・営業時間・定休日",
    status: missing.length === 0 ? "ok" : missing.length <= 1 ? "warn" : "ng",
    detail: missing.length === 0 ? "すべて記載されています" : `見つからない項目：${missing.join("、")}`,
    fix: missing.length === 0 ? "Googleマップや食べログと内容がそろっているかも確認しましょう。" : `${missing.join("・")}を文字で載せ、Googleマップや食べログと同じ内容にそろえましょう。`,
  });

  const types = jsonLdTypes(html);
  const hasLocal = types.some((t) => /Restaurant|LocalBusiness|FoodEstablishment|BarOrPub|CafeOrCoffeeShop/.test(t));
  checks.push({
    key: "jsonld",
    title: "AI向けの店舗データ（構造化データ）",
    status: hasLocal ? "ok" : "warn",
    detail: types.length ? `見つかった種類：${[...new Set(types)].join("、")}` : "設置されていません",
    fix: hasLocal ? undefined : "店名・住所・営業時間などを決まった形式（Restaurant）でページに埋め込むと、AIが情報を取り違えにくくなります。",
  });

  for (const scene of SCENES) {
    const found = scene.words.test(text);
    checks.push({
      key: `scene-${scene.key}`,
      title: `「${scene.label}」について書かれているか`,
      status: found ? "ok" : "warn",
      detail: found ? "記載があります" : "記載が見つかりません",
      fix: found ? undefined : `当てはまるなら一文書き足しましょう。例：「${scene.example}」`,
    });
  }

  try {
    const robots = await safeFetch(new URL("/robots.txt", page.url).toString());
    const blocked = robots.status < 400 ? blockedCrawlers(robots.body) : [];
    checks.push({
      key: "robots",
      title: "AIの読み取りを拒否していないか（robots.txt）",
      status: blocked.length ? "ng" : "ok",
      detail: blocked.length ? `拒否されているAI：${blocked.join("、")}` : "拒否していません",
      fix: blocked.length ? "robots.txt でAIの読み取りを拒否していると、AIの回答で紹介されにくくなります。" : undefined,
    });
  } catch {
    // robots.txt が無い・読めないのは「拒否していない」と同じ扱い
  }

  const points = { ok: 2, warn: 1, ng: 0 } as const;
  const score = Math.round((checks.reduce((s, c) => s + points[c.status], 0) / (checks.length * 2)) * 100);
  return { url: page.url, score, checks };
}
