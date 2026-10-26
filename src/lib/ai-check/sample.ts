import { addWebFixes, type AuditCheck } from "./audit";
import { pagePlansFor } from "./suggest";
import type { ReportData } from "@/components/ai/report-dashboard";

// 紹介ページとサンプルの報告書に使う、架空の求人サイトのデータ（実在のサービスではない）
const SAMPLE = {
  name: "リモ求人ナビ",
  genre: "求人サイト",
  audience: "子育て中の人",
  pitch: null,
  features: ["フルリモート", "未経験OK", "週3日から"],
  url: "https://remo-kyujin.example/",
};

const A = "求人サイトA（大手）";
const B = "在宅ワーク専門サイトB";
const C = "副業マッチングC";

const QUESTIONS: { text: string; hits: number; ranks: number[]; others: string[] }[] = [
  { text: "おすすめの求人サイトを5つ教えて", hits: 0, ranks: [], others: [A, C] },
  { text: "子育て中の人におすすめの求人サイトは？", hits: 1, ranks: [3], others: [A, B] },
  { text: "求人サイトを選ぶときのポイントと、おすすめのサービスは？", hits: 0, ranks: [], others: [A, B, C] },
  { text: "フルリモートの求人サイトを教えて", hits: 1, ranks: [2], others: [B] },
  { text: "子育て中の人向けで、フルリモートの求人サイトはある？", hits: 1, ranks: [1], others: [B] },
  { text: "未経験OKの求人サイトを教えて", hits: 0, ranks: [], others: [A, C] },
  { text: "子育て中の人向けで、未経験OKの求人サイトはある？", hits: 1, ranks: [2], others: [A] },
  { text: "週3日からの求人サイトを教えて", hits: 0, ranks: [], others: [A, B] },
  { text: "子育て中の人向けで、週3日からの求人サイトはある？", hits: 0, ranks: [], others: [B, C] },
  { text: "求人サイトを比較して、それぞれの特徴を教えて", hits: 0, ranks: [], others: [A, B] },
];

function sampleChecks(): AuditCheck[] {
  const checks: AuditCheck[] = [
    { key: "fetch", title: "公式サイトを読み込めるか", status: "ok", detail: `読み込めました（${SAMPLE.url}）` },
    { key: "jsonld", title: "AI向けのサービスデータ（構造化データ）", status: "ng", detail: "設置されていません" },
    { key: "pitch", title: "一文の立ち位置がトップページに書かれているか", status: "warn", detail: "「設定」で一文の立ち位置が登録されていません" },
    { key: "features", title: "強み（特徴の言葉）がトップページに文字で書かれているか", status: "warn", detail: "見つからない言葉：週3日から" },
    { key: "fresh", title: "更新日・確認日が書かれているか", status: "warn", detail: "日付の記載が見つかりません" },
    { key: "title", title: "題名・説明文で、何のサービスかがわかるか", status: "ok", detail: "題名：「リモ求人ナビ｜在宅・リモートの求人」" },
    { key: "text", title: "AIが読める文章の量", status: "ok", detail: "トップページの文章は約6,200文字" },
    { key: "pages", title: "固有の情報が1件1ページになっているか", status: "ok", detail: "トップページからリンクされたサイト内ページ：58件" },
    { key: "faq", title: "よくある質問（FAQ）があるか", status: "ok", detail: "見つかりました" },
    { key: "robots", title: "AIの読み取りを拒否していないか（robots.txt）", status: "ok", detail: "拒否していません" },
    { key: "sitemap", title: "サイトマップがあるか", status: "ok", detail: "見つかりました" },
    { key: "llms", title: "AI向けの案内ファイル（llms.txt）", status: "info", detail: "設置されていません" },
  ];
  addWebFixes(checks, {
    name: SAMPLE.name,
    category: SAMPLE.genre,
    audience: SAMPLE.audience,
    pitch: SAMPLE.pitch,
    features: SAMPLE.features,
    pageUrl: SAMPLE.url,
    title: "リモ求人ナビ｜在宅・リモートの求人",
    description: "在宅・リモートワークの求人情報を掲載しています。",
    missingFeatures: ["週3日から"],
  });
  return checks;
}

export function sampleReport(): ReportData {
  const questions = QUESTIONS.map((q, i) => ({ no: i + 1, text: q.text, hits: q.hits, total: 1, ranks: q.ranks, others: q.others }));
  const hits = questions.reduce((n, q) => n + q.hits, 0);
  const count = (name: string) => questions.filter((q) => q.others.includes(name)).length;
  const at = new Date("2026-10-20T10:00:00+09:00");
  return {
    name: `${SAMPLE.name}（サンプル）`,
    url: SAMPLE.url,
    createdAt: at,
    description: "架空の求人サイトのデモです。ChatGPTとGeminiに、見込み客が聞きそうな質問10問を聞き、紹介されたか・何番目か・どのサイトを根拠にしたかを記録しました。",
    measured: { hits, total: questions.length, at },
    score: 79,
    questions,
    ranking: [
      { label: SAMPLE.name, hits, self: true, registered: false },
      { label: A, hits: count(A), self: false, registered: true },
      { label: B, hits: count(B), self: false, registered: true },
      { label: C, hits: count(C), self: false, registered: false },
    ],
    sources: [
      { domain: "compare-media.example", count: 7, kind: "other" },
      { domain: "kyujin-a.example", count: 6, kind: "other" },
      { domain: "remo-kyujin.example", count: 3, kind: "official" },
      { domain: "note.com", count: 2, kind: "platform" },
    ],
    trend: [
      { label: "9/22", rate: 20 },
      { label: "9/29", rate: 20 },
      { label: "10/6", rate: 30 },
      { label: "10/13", rate: 30 },
      { label: "10/20", rate: Math.round((hits / questions.length) * 100) },
    ],
    checks: sampleChecks(),
    plans: pagePlansFor(
      questions.filter((q) => q.hits === 0).map((q) => q.text),
      SAMPLE,
    ),
  };
}
