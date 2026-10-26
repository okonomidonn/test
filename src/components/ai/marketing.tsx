// 紹介ページ・料金・計測のしかたで共用する説明部品

// 計測の流れ（3ステップの図）
export function MethodSteps() {
  const steps = [
    { title: "AIに質問する", body: "見込み客が聞きそうな質問を作り、ChatGPT・Geminiに聞きます" },
    { title: "競合と比べる", body: "紹介されたか・何番目か・どのサイトが根拠かを、競合と並べて記録します" },
    { title: "直す点を整理する", body: "直すページと優先順位、事前に確かめることを提案します" },
  ];
  return (
    <ol className="grid overflow-hidden rounded-xl border border-slate-200 md:grid-cols-3">
      {steps.map((s, i) => (
        <li key={s.title} className={`relative min-w-0 bg-white p-5 ${i ? "border-t border-slate-200 md:border-l md:border-t-0" : ""}`}>
          <p className="font-num text-sm font-semibold text-brand">STEP {i + 1}</p>
          <p className="mt-1 text-lg font-bold text-slate-900">{s.title}</p>
          <p className="mt-1 text-slate-700">{s.body}</p>
          {i < steps.length - 1 && (
            <span className="absolute -right-3 top-1/2 z-10 hidden h-6 w-6 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-sm text-slate-500 md:grid" aria-hidden>
              →
            </span>
          )}
        </li>
      ))}
    </ol>
  );
}

// 相場との比較（出典を確認した数字だけを使う）
export const MARKET = {
  source: "AI検索ドック「AIO対策・LLMO対策の費用相場は？ 公開料金95件と主なサービスの料金一覧」",
  url: "https://stellagent.ai/ja/ai-search-dock/blog/ai-search-cost",
  checked: "2026年10月6日確認・公式ページの公開料金95件",
  diagnosisMedian: 150000,
  monthlyMedian: 225000,
};

export function MarketCompare() {
  const rows = [
    { label: "1回の診断", market: MARKET.diagnosisMedian, ours: 49800, oursLabel: "GEO診断・改善トライアル", unit: "", note: "診断に加えて、1ページの改善と30日後の再計測まで" },
    { label: "月額の対策支援", market: MARKET.monthlyMedian, ours: 98000, oursLabel: "継続支援（〜）", unit: "／月", note: "毎月の計測と、ページの作成・書き換え（月4ページ〜）" },
  ];
  const yen = (n: number) => `${n.toLocaleString("ja-JP")}円`;
  return (
    <div className="space-y-4">
      <div className="grid gap-4 md:grid-cols-2">
        {rows.map((r) => (
          <div key={r.label} className="min-w-0 rounded-xl border border-slate-200 bg-white p-5">
            <p className="font-semibold text-slate-900">{r.label}</p>
            <dl className="mt-4 space-y-3">
              {[
                { k: "公開料金の中央値", v: r.market, cls: "bg-slate-300" },
                { k: `AIススメル：${r.oursLabel}`, v: r.ours, cls: "bg-brand" },
              ].map((x) => (
                <div key={x.k}>
                  <div className="flex items-baseline justify-between gap-3 text-sm">
                    <dt className="text-slate-700">{x.k}</dt>
                    <dd className="font-num font-semibold text-slate-900">
                      {yen(x.v)}
                      {r.unit}
                    </dd>
                  </div>
                  <div className="mt-1 h-2.5 rounded-full bg-slate-100">
                    <div className={`h-2.5 rounded-full ${x.cls}`} style={{ width: `${(x.v / r.market) * 100}%` }} />
                  </div>
                </div>
              ))}
            </dl>
            <p className="mt-3 text-sm text-slate-600">{r.note}</p>
          </div>
        ))}
      </div>
      <p className="text-sm text-slate-600">
        出典：
        <a href={MARKET.url} target="_blank" rel="noopener noreferrer" className="underline">
          {MARKET.source}
        </a>
        （{MARKET.checked}）。各社の作業範囲・税の扱いは異なり、同じ内容・同じ成果を比べたものではありません。AIススメルの料金は税別です。
      </p>
    </div>
  );
}

// この価格でできる理由
export const WHY_AFFORDABLE: [string, string][] = [
  ["計測を自社のツールで自動化", "質問づくり・AIへの質問・記録・サイト診断を自社開発のツールで行い、人の作業を分析と改善に絞っています"],
  ["対象と作業を絞っている", "中小企業のWebサービスに絞り、トライアルは既存ページ1ページの改善までと範囲を決めています"],
  ["効果を確かめてから続ける", "30日後に同じ条件で測り直し、続けるかどうかを数字を見て決められます"],
];
