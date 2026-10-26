import { addWebFixes, type AuditCheck } from "./audit";
import { pagePlansFor } from "./suggest";
import type { ReportData } from "@/components/ai/report-view";

// 紹介ページとサンプルの報告書に使う、架空の求人サイトのデータ（実在のサービスではない）
const SAMPLE = {
  name: "リモ求人ナビ",
  genre: "求人サイト",
  audience: "子育て中の人",
  pitch: null,
  features: ["フルリモート", "未経験OK", "週3日から"],
  url: "https://remo-kyujin.example/",
};

const QUESTIONS: { text: string; hits: number; ranks: number[] }[] = [
  { text: "おすすめの求人サイトを5つ教えて", hits: 0, ranks: [] },
  { text: "子育て中の人におすすめの求人サイトは？", hits: 1, ranks: [3] },
  { text: "求人サイトを選ぶときのポイントと、おすすめのサービスは？", hits: 0, ranks: [] },
  { text: "フルリモートの求人サイトを教えて", hits: 1, ranks: [2] },
  { text: "子育て中の人向けで、フルリモートの求人サイトはある？", hits: 1, ranks: [1] },
  { text: "未経験OKの求人サイトを教えて", hits: 0, ranks: [] },
  { text: "子育て中の人向けで、未経験OKの求人サイトはある？", hits: 1, ranks: [2] },
  { text: "週3日からの求人サイトを教えて", hits: 0, ranks: [] },
  { text: "子育て中の人向けで、週3日からの求人サイトはある？", hits: 0, ranks: [] },
  { text: "求人サイトを比較して、それぞれの特徴を教えて", hits: 0, ranks: [] },
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
  const questions = QUESTIONS.map((q, i) => ({ no: i + 1, text: q.text, hits: q.hits, total: 1, ranks: q.ranks }));
  const hits = questions.reduce((n, q) => n + q.hits, 0);
  const at = new Date("2026-10-20T10:00:00+09:00");
  return {
    name: `${SAMPLE.name}（サンプル）`,
    url: SAMPLE.url,
    createdAt: at,
    measured: { hits, total: questions.length, at },
    score: 79,
    questions,
    others: [
      { label: "求人サイトA（大手）", count: 8, registered: true },
      { label: "在宅ワーク専門サイトB", count: 6, registered: true },
      { label: "副業マッチングC", count: 4, registered: false },
    ],
    sources: [
      { domain: "compare-media.example", count: 7, official: false },
      { domain: "kyujin-a.example", count: 6, official: false },
      { domain: "remo-kyujin.example", count: 3, official: true },
      { domain: "note.com", count: 2, official: false },
    ],
    checks: sampleChecks(),
    plans: pagePlansFor(
      questions.filter((q) => q.hits === 0).map((q) => q.text),
      SAMPLE,
    ),
  };
}
