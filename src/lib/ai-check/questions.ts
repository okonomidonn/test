// 店名・サービス名を含まない「お客さんが実際にAIに聞きそうな質問」を作る。
// 飲食店はエリアと業態から、Webサービスはカテゴリ・対象・強みから作る。

// 店舗の数・月の計測回数・質問数の上限はプランごとに決まる（src/lib/billing/plans.ts）
export const OWNER_LIMITS = {
  repeats: 1,
  // 店舗オーナーが続けて計測できる間隔（API費用の上限を守るため）
  runIntervalHours: 20,
};

// サービス全体で1日に取得する回答数の上限（1回答あたり数十円かかるため）。GEO_DAILY_ANSWER_LIMIT で変更できる。
export function dailyAnswerLimit(): number {
  return Number(process.env.GEO_DAILY_ANSWER_LIMIT) || 300;
}

export const TEAM_MAX_REPEATS = 3;

export function defaultQuestions(input: { area: string; station?: string | null; genre: string }): string[] {
  const area = input.area.trim();
  const near = input.station?.trim() || `${area}駅`;
  const food = input.genre.split(/[・、,／/ ]/)[0]?.trim() || input.genre.trim();
  const kind = /居酒屋|バー|酒場/.test(input.genre) ? "居酒屋" : "お店";

  return [
    `${near}周辺でおすすめの${kind}を5店教えて`,
    `${area}で${food}がおいしいお店は？`,
    `${area}でデートにおすすめの${kind}は？`,
    `${near}近くで一人飲みにおすすめの店は？`,
    `${area}で地元の食材を使った料理が食べられる店は？`,
    `${area}で落ち着いて飲める${kind}は？`,
    `${near}周辺でお酒がおいしいお店は？`,
    `${area}で予算5,000円のおすすめディナーは？`,
    `${area}で料理がおいしい隠れ家的な${kind}は？`,
    `${area}で少人数の飲み会におすすめの店は？`,
  ];
}

// Webサービス向け。大きい質問（おすすめは？）に加え、強みごとの細かい質問を作る。
// 細かい質問ほど答えを持つサイトが少なく、新しいサービスでも紹介されやすい（zaitoの事例）。
export function webQuestions(input: { genre: string; audience?: string | null; features?: string[] }): string[] {
  const category = input.genre.trim();
  const who = input.audience?.trim();
  const features = (input.features ?? []).map((f) => f.trim()).filter(Boolean);

  const broad = [
    `おすすめの${category}を5つ教えて`,
    who ? `${who}におすすめの${category}は？` : `初心者におすすめの${category}は？`,
    `${category}を選ぶときのポイントと、おすすめのサービスは？`,
    `${category}を比較して、それぞれの特徴を教えて`,
  ];
  const specific = features.flatMap((f) => [
    `${f}の${category}を教えて`,
    who ? `${who}向けで、${f}の${category}はある？` : `${f}で評判のいい${category}は？`,
  ]);
  const fillers = [`最近できた${category}でおすすめは？`, `評判のいい${category}は？`, `無料で使える${category}は？`];

  const all = [...broad, ...specific, ...fillers];
  return [...new Set(all)].slice(0, 10);
}

// 店舗オーナーが次に計測できるまでの残り時間（ミリ秒）。0なら今すぐ計測できる。
export function ownerCooldownMs(lastRunAt: Date | undefined): number {
  if (!lastRunAt) return 0;
  return Math.max(0, lastRunAt.getTime() + OWNER_LIMITS.runIntervalHours * 3600_000 - Date.now());
}
