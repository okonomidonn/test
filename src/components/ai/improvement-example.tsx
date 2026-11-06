export function ImprovementExample() {
  return (
    <section aria-labelledby="improvement-example-title">
      <h3 id="improvement-example-title" className="text-xl font-bold text-slate-900">「何を直すか」は、文章でお渡しします</h3>
      <p className="mt-2 text-xs leading-relaxed text-slate-500">架空の求人サイトの例です。実際の顧客事例・計測結果ではなく、改善による効果を示すものではありません。</p>
      <div className="mt-5 grid gap-3 md:grid-cols-2">
        <div className="rounded-md bg-white p-6"><h3 className="text-sm font-bold text-slate-600">修正前の文章</h3><p className="mt-3 text-lg font-bold text-slate-900">あなたにぴったりの仕事が見つかる求人サイト。</p><p className="mt-3 text-sm leading-relaxed text-slate-700">誰が、どんな仕事を探せるサイトかが読み取れません。</p></div>
        <div className="rounded-md border-2 border-brand bg-white p-6"><h3 className="text-sm font-bold text-brand">ご提案する文章</h3><p className="mt-3 text-lg font-bold leading-relaxed text-slate-900">関西の飲食店で、未経験から始められるアルバイトを集めた求人サイト。</p><p className="mt-3 text-sm leading-relaxed text-slate-700">対象の人・地域・仕事の特徴を、確認できた事実に基づいて具体的に書きます。</p></div>
      </div>
      <ol className="mt-3 grid gap-3 text-sm text-slate-700 sm:grid-cols-3">
        <li className="rounded-md bg-white p-5"><strong className="block text-slate-900">修正案をお渡し</strong><p className="mt-1">対象URL、変更する文章、変更の理由をまとめます。</p></li>
        <li className="rounded-md bg-white p-5"><strong className="block text-slate-900">ご確認後に反映</strong><p className="mt-1">事実や表現をご確認いただき、CMSで反映します。有料プランの作業です。</p></li>
        <li className="rounded-md bg-white p-5"><strong className="block text-slate-900">記録して再確認</strong><p className="mt-1">変更箇所と、再計測時の紹介状況を報告します。</p></li>
      </ol>
    </section>
  );
}

export function ImprovementScope() {
  return (
    <section id="scope" className="scroll-mt-24 rounded-md bg-white p-7 sm:p-9">
      <h3 className="text-xl font-bold text-slate-900">「既存ページの改善1件」に含むこと</h3>
      <p className="mt-3 leading-relaxed text-slate-700">1つの既存URLを対象に、文章・見出し・FAQの修正案を作り、承認後にCMSで反映するまでを1件とします。画面デザインの作り直しや機能開発は含みません。</p>
      <ul className="mt-5 space-y-2 border-t border-slate-200 pt-5 text-sm leading-relaxed text-slate-700">
        <li>対象URL・原稿量・修正回数・公開までの担当範囲は、契約前の見積書で固定します。</li>
        <li>制作プランの新規ページは月2本・各3,000字まで。既存のCMS・テンプレートを使う範囲です。</li>
        <li>取材・撮影・画像制作・独自デザイン・新機能・大規模な実装・外部媒体への掲載は対象外です。追加が必要なら、別途見積もりに合意してから着手します。</li>
        <li>お客様には、サービス情報の提供、原稿・公開の承認、必要なCMS権限のご用意をお願いします。GA4は閲覧できる範囲で確認します。</li>
      </ul>
    </section>
  );
}
