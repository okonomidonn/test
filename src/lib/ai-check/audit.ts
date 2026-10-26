import { lookup } from "node:dns/promises";
import { isIP } from "node:net";

// 店舗・サービスの公式サイトを読み、AIに伝わりにくい点を洗い出す。
// URLは利用者が入力するため、社内ネットワーク等へのアクセスに使われないよう宛先を検査する。

// info：参考情報（点数に入れない）
export type CheckStatus = "ok" | "warn" | "ng" | "info";
export type AuditCheck = {
  key: string;
  title: string;
  status: CheckStatus;
  detail: string;
  fix?: string;
  // 具体的な直し方（Webサービスの診断のみ）：直す場所・今の内容・そのまま使える書き換え例
  where?: string;
  current?: string;
  example?: string;
  exampleLabel?: string;
  // 指摘の種類：tech＝確かめた技術的な問題、info＝情報が足りない（事実を確認してから書く）、hypothesis＝効果が確かでない改善の仮説
  kind?: "tech" | "info" | "hypothesis";
  // 直す前・直した後に確かめること
  verify?: string;
};

export const FIX_KIND = {
  tech: { label: "技術的な問題", note: "サイトを読み取って確かめた事実です" },
  info: { label: "情報不足・要確認", note: "書き足す前に、内容が事実か確認してください" },
  hypothesis: { label: "改善の仮説", note: "効果は確かではありません。直したあと計測して確かめます" },
} as const;

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

  await checkRobots(page.url, checks);
  return { url: page.url, score: scoreOf(checks), checks };
}

// robots.txt を確認して checks に追加し、本文を返す（無い・読めない場合は空文字）
async function checkRobots(pageUrl: string, checks: AuditCheck[]): Promise<string> {
  try {
    const robots = await safeFetch(new URL("/robots.txt", pageUrl).toString());
    const body = robots.status < 400 ? robots.body : "";
    const blocked = blockedCrawlers(body);
    checks.push({
      key: "robots",
      title: "AIの読み取りを拒否していないか（robots.txt）",
      status: blocked.length ? "ng" : "ok",
      detail: blocked.length ? `拒否されているAI：${blocked.join("、")}` : "拒否していません",
      fix: blocked.length
        ? "robots.txt でAIの読み取りを拒否していると、AIの回答で紹介されにくくなります。特にChatGPTの検索に載るには OAI-SearchBot を許可してください。"
        : undefined,
    });
    return body;
  } catch {
    // robots.txt が無い・読めないのは「拒否していない」と同じ扱い
    return "";
  }
}

function scoreOf(checks: AuditCheck[]): number {
  const points = { ok: 2, warn: 1, ng: 0 } as const;
  const scored = checks.filter((c): c is AuditCheck & { status: keyof typeof points } => c.status !== "info");
  return Math.round((scored.reduce((s, c) => s + points[c.status], 0) / (scored.length * 2)) * 100);
}

async function fetchOk(url: string): Promise<{ ok: boolean; body: string }> {
  try {
    const res = await safeFetch(url);
    return { ok: res.status < 400, body: res.body };
  } catch {
    return { ok: false, body: "" };
  }
}

// 同じサイト内へのリンク（ページ数の目安）。固有の情報を1件1ページで持っているかを見る。
function internalLinks(html: string, pageUrl: string): string[] {
  const base = new URL(pageUrl);
  const urls = new Set<string>();
  for (const m of html.matchAll(/<a\s[^>]*href=["']([^"'#]+)["']/gi)) {
    try {
      const u = new URL(m[1], base);
      if (u.hostname !== base.hostname || !/^https?:$/.test(u.protocol)) continue;
      if (/\.(png|jpe?g|gif|svg|webp|pdf|zip)$/i.test(u.pathname)) continue;
      urls.add(u.pathname.replace(/\/$/, "") || "/");
    } catch {
      // 不正なURLは無視する
    }
  }
  urls.delete("/");
  return [...urls];
}

const squash = (s: string) => s.normalize("NFKC").replace(/[\s　、。,.!！?？「」]/g, "");

// Webサービス向けの診断。zaitoの事例で効いた作り（一文の立ち位置・1件1ページ・構造化データ・llms.txt）を確認する。
type WebFixContext = {
  name: string;
  category: string;
  audience?: string | null;
  pitch?: string | null;
  features: string[];
  pageUrl: string;
  title: string;
  description: string;
  missingFeatures: string[];
};

// 各項目に「どこを・どう直すか」を、サイトの実際の内容と登録情報から具体的に書き足す
export function addWebFixes(checks: AuditCheck[], c: WebFixContext) {
  const origin = new URL(c.pageUrl).origin;
  const who = c.audience?.trim() ? `${c.audience.trim()}向けの` : "";
  const strengths = c.features.slice(0, 3).join("・");
  // 求人サイトなら「求人」、それ以外は「サービス」と呼ぶ
  const noun = /求人|仕事|バイト|転職|インターン/.test(c.category) ? "求人" : "サービス";
  const top2 = c.features.slice(0, 2).join("・");
  const oneLine = c.pitch?.trim() || `${who}${top2 ? `${top2}の` : ""}${c.category}`;
  const shortLine = c.pitch?.trim() || `${c.features[0] ? `${c.features[0]}の` : ""}${c.category}`;
  const today = new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", year: "numeric", month: "long", day: "numeric" }).format(new Date());

  const fixes: Record<string, Partial<AuditCheck>> = {
    title: {
      where: `トップページ（${c.pageUrl}）の題名（<title>）と説明文（<meta name="description">）`,
      current: `題名：${c.title || "なし"}\n説明文：${c.description || "なし"}`,
      exampleLabel: "書き換え例",
      example: `題名：${shortLine}｜${c.name}\n説明文：${c.name}は、${who}${c.category}です。${strengths ? `${strengths}など、` : ""}（ほかのサービスとの違いを1文で）。`,
    },
    pitch: {
      where: `トップページ（${c.pageUrl}）の一番上の見出しのすぐ下。同じ文をFAQ・会社概要にも`,
      exampleLabel: "そのまま使える一文",
      example: `${c.name}は、${oneLine}です。`,
    },
    features: c.missingFeatures.length
      ? {
          where: `トップページ（${c.pageUrl}）の本文（画像の中の文字ではなく、文章として）`,
          current: `見つからない言葉：${c.missingFeatures.join("、")}`,
          exampleLabel: "書き足す文の例",
          example: `（「${c.missingFeatures.join("」「")}」に当てはまる${noun}が実際にある場合だけ）\n${c.name}では、「${c.missingFeatures.join("」「")}」の${noun}を掲載しています。（件数や具体例を1つ添える。例：〇件、〇〇など）`,
        }
      : {},
    text: {
      where: `トップページ（${c.pageUrl}）`,
      exampleLabel: "やること",
      example: "・画像の中に書いた説明を、本文の文章としても書く\n・JavaScriptで後から表示している一覧は、最初のHTMLに含める（サーバー側で出力する）",
    },
    pages: {
      where: `サイト全体（${origin}）`,
      exampleLabel: "作るページの例（1つの話題に1ページ）",
      example: [
        ...c.features.slice(0, 3).map((f) => `・${f}の${noun}一覧（${origin}/…）`),
        `・${c.name}と他の${c.category}の違い`,
        "・利用者の事例・声（1件1ページ）",
        "・料金・使い方・よくある質問",
      ].join("\n"),
    },
    jsonld: {
      where: `トップページ（${c.pageUrl}）の <head> の中`,
      exampleLabel: "そのまま貼れるコード（中身を確認してから使ってください）",
      example: `<script type="application/ld+json">
${JSON.stringify(
  {
    "@context": "https://schema.org",
    "@graph": [
      { "@type": "Organization", "@id": `${origin}/#org`, name: c.name, url: `${origin}/` },
      { "@type": "WebSite", "@id": `${origin}/#website`, name: c.name, url: `${origin}/`, description: `${c.name}は、${oneLine}です。`, publisher: { "@id": `${origin}/#org` } },
    ],
  },
  null,
  2,
)}
</script>`,
    },
    faq: {
      where: `新しいページ（例：${origin}/faq/）と、トップページからのリンク`,
      exampleLabel: "質問と答えの例（答えは事実に合わせて書き換える）",
      example: [
        `Q. ${c.name}はどんなサービスですか？\nA. ${c.name}は、${oneLine}です。`,
        ...c.features.slice(0, 2).map((f) => `Q. ${f}の${noun}はありますか？\nA. はい。（件数・条件・具体例）`),
        `Q. 他の${c.category}との違いは？\nA. （${c.name}だけの特徴を1〜2文で）`,
      ].join("\n\n"),
    },
    fresh: {
      where: `トップページ（${c.pageUrl}）の一覧の上、または下`,
      exampleLabel: "書き足す一行の例",
      example: `最終更新日：${today}（掲載〇件）`,
    },
    robots: {
      where: `${origin}/robots.txt`,
      exampleLabel: "追記する内容（AIの検索用ロボットの読み取りを許可）",
      example: "User-agent: OAI-SearchBot\nAllow: /\n\nUser-agent: ChatGPT-User\nAllow: /\n\nUser-agent: PerplexityBot\nAllow: /",
    },
    sitemap: {
      where: `${origin}/robots.txt と、Bing Webmaster Tools（https://www.bing.com/webmasters）`,
      exampleLabel: "robots.txt に書く一行",
      example: `Sitemap: ${origin}/sitemap.xml`,
    },
  };

  // 指摘の種類と、確かめること
  const meta: Record<string, Pick<AuditCheck, "kind" | "verify">> = {
    fetch: { kind: "tech", verify: "ブラウザでURLを開けるか、エラーが出ないかを確認する" },
    robots: { kind: "tech", verify: "直したあと、robots.txt をブラウザで開いて内容を確認する" },
    sitemap: { kind: "tech", verify: "Bing Webmaster Tools でサイトマップが読み込まれたかを確認する" },
    text: { kind: "tech", verify: "ページのソースを表示して、説明の文章がHTMLに含まれているかを確認する" },
    title: { kind: "tech", verify: "書き換えた題名が、実際のサービス内容と合っているかを確認する" },
    pitch: { kind: "info", verify: "一文が事実か（本当に他社と違うか）を社内で確認してから載せる" },
    features: { kind: "info", verify: "書き足す言葉に当てはまる内容（求人・機能など）が実際にあるかを確認する。なければ書かない" },
    faq: { kind: "info", verify: "答えの内容が事実か、問い合わせの多い質問かを確認する" },
    fresh: { kind: "info", verify: "日付と件数が正しいかを確認し、更新するたびに書き換える" },
    pages: { kind: "info", verify: "既存のページで答えられないかを先に確認する。同じ内容のページを増やさない" },
    jsonld: { kind: "hypothesis", verify: "入れたあと、同じ質問で計測し直して変化を見る。変化がなくても問題はない" },
    llms: { kind: "hypothesis", verify: "必須ではない" },
  };

  for (const check of checks) {
    Object.assign(check, meta[check.key] ?? {});
    if (check.status === "ok" && check.key !== "sitemap") continue;
    Object.assign(check, fixes[check.key] ?? {});
  }
}

export async function auditWebSite(input: {
  websiteUrl: string;
  name: string;
  genre: string;
  audience?: string | null;
  pitch?: string | null;
  features?: string[];
}) {
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
  const category = input.genre.trim();

  checks.push({
    key: "fetch",
    title: "公式サイトを読み込めるか",
    status: page.status < 400 ? "ok" : "ng",
    detail: page.status < 400 ? `読み込めました（${page.url}）` : `エラー（HTTP ${page.status}）`,
  });

  const head = squash(title + description);
  // 「求人サイト」なら「求人」があれば十分とみなす（題名では「サイト」「サービス」を省くことが多いため）
  const core = squash(category).replace(/(サイト|サービス|アプリ|ツール|ソフト)$/, "") || squash(category);
  const hasCategory = head.includes(core);
  checks.push({
    key: "title",
    title: "題名・説明文で、何のサービスかがわかるか",
    status: hasCategory && description ? "ok" : "warn",
    detail: `題名：「${title || "なし"}」／説明文：${description ? `「${description.slice(0, 80)}」` : "なし"}`,
    fix: hasCategory && description ? undefined : `題名か説明文（meta description）に「${category}」と、誰向けの何のサービスかを一文で入れましょう。`,
  });

  const pitch = input.pitch?.trim();
  const pitchFound = pitch ? squash(text).includes(squash(pitch)) : false;
  checks.push({
    key: "pitch",
    title: "一文の立ち位置がトップページに書かれているか",
    status: pitchFound ? "ok" : "warn",
    detail: pitch ? (pitchFound ? `「${pitch}」が書かれています` : `「${pitch}」がトップページに見つかりません`) : "「設定」で一文の立ち位置が登録されていません",
    fix: pitchFound
      ? "FAQ・会社概要・llms.txtでも同じ言葉を使いましょう。"
      : "「誰の・何を・他と何が違うか」を一文で決め、トップ・FAQ・会社概要・llms.txtに同じ言葉で書きましょう。AIが紹介文にそのまま使えます。",
  });

  const features = (input.features ?? []).filter(Boolean);
  let missingFeatures: string[] = [];
  if (features.length) {
    missingFeatures = features.filter((f) => !squash(text).includes(squash(f)));
    const missing = missingFeatures;
    checks.push({
      key: "features",
      title: "強み（特徴の言葉）がトップページに文字で書かれているか",
      status: missing.length === 0 ? "ok" : missing.length < features.length ? "warn" : "ng",
      detail: missing.length === 0 ? "すべて書かれています" : `見つからない言葉：${missing.join("、")}`,
      fix: missing.length === 0 ? undefined : "利用者がAIに聞くときの言葉で書きましょう。言葉が違うと、AIは当てはまるサービスだと判断できません。",
    });
  }

  checks.push({
    key: "text",
    title: "AIが読める文章の量",
    status: text.length >= 1500 ? "ok" : text.length >= 500 ? "warn" : "ng",
    detail: `トップページの文章は約${text.length.toLocaleString()}文字`,
    fix: text.length >= 1500 ? undefined : "文章が少ないか、画像やプログラム（JavaScript）で後から表示されているため、AIに内容が伝わりにくい状態です。説明を文字で書き足しましょう。",
  });

  const links = internalLinks(html, page.url);
  checks.push({
    key: "pages",
    title: "固有の情報が1件1ページになっているか",
    status: links.length >= 30 ? "ok" : links.length >= 10 ? "warn" : "ng",
    detail: `トップページからリンクされたサイト内ページ：${links.length}件`,
    fix:
      links.length >= 30
        ? undefined
        : "事例・機能・料金・対応範囲・比較などを、それぞれ独立したページにしましょう。AIは細かい質問ほど、答えがぴったり書かれた1ページを紹介します。",
  });

  const types = [...new Set(jsonLdTypes(html))];
  const hasOrg = types.some((t) => /Organization|Corporation|WebSite/.test(t));
  const hasContent = types.some((t) => /SoftwareApplication|WebApplication|Product|Service|JobPosting|ItemList|Course|Offer|Article/.test(t));
  checks.push({
    key: "jsonld",
    title: "AI向けのサービスデータ（構造化データ）",
    // 入れれば紹介が増えるとは限らない（改善の仮説）ため、なくても「直すとよい」にとどめる
    status: hasOrg && hasContent ? "ok" : "warn",
    detail: types.length ? `見つかった種類：${types.join("、")}` : "設置されていません",
    fix:
      hasOrg && hasContent
        ? undefined
        : "運営者（Organization・WebSite）と、サービスの中身（SoftwareApplication・Product・Service・JobPostingなど）を決まった形式でページに埋め込むと、AIが内容を取り違えにくくなります。",
  });

  const hasFaq = types.some((t) => t === "FAQPage") || /よくある質問|よくあるご質問|FAQ|Q&A/i.test(text);
  checks.push({
    key: "faq",
    title: "よくある質問（FAQ）があるか",
    status: hasFaq ? "ok" : "warn",
    detail: hasFaq ? "見つかりました" : "トップページから見つかりません",
    fix: hasFaq ? undefined : "「〇〇と△△の違いは？」「安全？」「料金は？」など、利用者がAIに聞きそうな質問と答えをまとめたページを作りましょう。",
  });

  const updated = /最終更新|更新日|確認日|20\d{2}[年/.-]\s?\d{1,2}[月/.-]/.test(text);
  checks.push({
    key: "fresh",
    title: "更新日・確認日が書かれているか",
    status: updated ? "ok" : "warn",
    detail: updated ? "日付の記載があります" : "日付の記載が見つかりません",
    fix: updated ? undefined : "「最終更新日」「〇件掲載（〇月〇日時点）」などを書くと、情報が新しいことがAIに伝わります。",
  });

  const llms = await fetchOk(new URL("/llms.txt", page.url).toString());
  const llmsValid = llms.ok && !/<html/i.test(llms.body.slice(0, 500)) && llms.body.trim().length > 50;
  // llms.txt は、使っていないと公表しているAI（Googleの生成AI機能など）もあるため点数には入れない
  checks.push({
    key: "llms",
    title: "AI向けの案内ファイル（llms.txt）",
    status: "info",
    detail: llmsValid ? "設置されています" : "設置されていません",
    fix: llmsValid ? undefined : "必須ではありません。ほかの項目を先に直しましょう。",
  });

  const robots = await checkRobots(page.url, checks);
  const hasSitemap = /^\s*sitemap\s*:/im.test(robots) || (await fetchOk(new URL("/sitemap.xml", page.url).toString())).ok;
  checks.push({
    key: "sitemap",
    title: "サイトマップがあるか",
    status: hasSitemap ? "ok" : "warn",
    detail: hasSitemap ? "見つかりました" : "robots.txt にも /sitemap.xml にも見つかりません",
    fix: hasSitemap
      ? "Bing Webmaster Tools にも登録しましょう（ChatGPTの検索はBingの情報も使います）。"
      : "サイトマップを作り、Google Search Console と Bing Webmaster Tools に登録しましょう。",
  });

  addWebFixes(checks, {
    name: input.name,
    category,
    audience: input.audience,
    pitch: input.pitch,
    features,
    pageUrl: page.url,
    title,
    description,
    missingFeatures,
  });
  return { url: page.url, score: scoreOf(checks), checks };
}
