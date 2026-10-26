// 紹介されなかった質問ごとに「どんなページを作れば答えになるか」を具体的に示す。
// AIは、質問にぴったり答えているページを紹介しやすい（zaitoでは個別の求人ページが紹介された）。

export type PagePlan = {
  question: string;
  // 同じページで答えられる質問（比較ページは、広い質問をまとめて1つにする）
  questions: string[];
  type: "比較・選び方のページ" | "条件に答えるページ";
  title: string;
  firstLine: string;
  include: string[];
};

type Target = { name: string; genre: string; audience?: string | null; features: string[] };

// 質問の文末（「を教えて」「は？」など）を外して、ページの主題にする
function subjectOf(question: string): string {
  return question
    .replace(/[?？。]+$/, "")
    .replace(/(を\d+つ教えて|を教えて|はある|はありますか|でおすすめは|を比較して、それぞれの特徴を教えて|を選ぶときのポイントと、おすすめのサービスは)$/, "")
    .replace(/^(最近できた|評判のいい|無料で使える)/, "")
    .trim();
}

export function pagePlanFor(question: string, t: Target): PagePlan {
  const subject = subjectOf(question);
  const who = t.audience?.trim();
  const broad = /おすすめ|比較|選ぶ|評判|最近できた/.test(question) && !t.features.some((f) => question.includes(f));

  if (broad) {
    return {
      question,
      questions: [question],
      type: "比較・選び方のページ",
      title: `${t.genre}の選び方と比較｜${t.name}`,
      firstLine: `${t.genre}を選ぶときは、（条件1）（条件2）（条件3）を確かめましょう。${t.name}は${who ? `${who}のうち、` : ""}（${t.name}が向いている人）に向いています。`,
      include: [
        "選ぶときの基準を3〜5個（料金・対象・条件など）",
        `主な${t.genre}の比較表（自社も含めて、違いを事実で）`,
        `${t.name}が向いている人・向いていない人`,
        "最終更新日",
      ],
    };
  }

  const matched = t.features.find((f) => question.includes(f));
  return {
    question,
    questions: [question],
    type: "条件に答えるページ",
    title: `${subject}｜${t.name}`,
    firstLine: `${t.name}では、${matched ? `「${matched}」に当てはまる` : `${subject}に当てはまる`}${/求人|仕事|バイト|転職|インターン/.test(t.genre) ? "求人" : "内容"}を〇件掲載しています（〇年〇月〇日時点）。`,
    include: [
      "その条件に当てはまる一覧（件数と、代表的な例を3つ）",
      `条件の定義（${matched ?? subject}とは何か、どう確認しているか）`,
      who ? `${who}がよく気にする点への答え（Q&Aを2〜3個）` : "よくある質問への答え（Q&Aを2〜3個）",
      "最終更新日・確認日",
    ],
  };
}

// 紹介されなかった質問すべてについて、作るページの案を出す。同じ題名になる案は1つにまとめる
export function pagePlansFor(questions: string[], t: Target): PagePlan[] {
  const byTitle = new Map<string, PagePlan>();
  for (const q of questions) {
    const plan = pagePlanFor(q, t);
    const same = byTitle.get(plan.title);
    if (same) same.questions.push(q);
    else byTitle.set(plan.title, plan);
  }
  return [...byTitle.values()];
}
