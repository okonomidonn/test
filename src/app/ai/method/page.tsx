import Link from "next/link";
import { btn } from "@/components/ai/ds";
import { MethodSteps } from "@/components/ai/marketing";

export const metadata = { title: "計測のしかたと進め方 | AIススメル", description: "AIススメルの計測方法、数字の意味、限界、作業の範囲、情報の取り扱い" };

const SECTIONS = [
  { id: "measure", label: "計測のしかた" },
  { id: "numbers", label: "数字の意味" },
  { id: "limits", label: "限界" },
  { id: "scope", label: "サイトをどこまで直すか" },
  { id: "flow", label: "申し込み後の流れ" },
  { id: "privacy", label: "情報の取り扱い" },
] as const;

function Block({ id, title, children }: { id: string; title: string; children: React.ReactNode }) {
  return (
    <section id={id} className="scroll-mt-24 border-t border-slate-200 py-8 first:border-t-0 first:pt-0">
      <h2 className="text-xl font-bold text-slate-900">{title}</h2>
      <div className="mt-4 space-y-3 text-slate-800">{children}</div>
    </section>
  );
}

function Rows({ rows }: { rows: [string, string][] }) {
  return (
    <dl className="divide-y divide-slate-200 border-y border-slate-200">
      {rows.map(([k, v]) => (
        <div key={k} className="grid gap-1 py-3 sm:grid-cols-[12rem_1fr] sm:gap-6">
          <dt className="font-semibold text-slate-900">{k}</dt>
          <dd className="text-slate-700">{v}</dd>
        </div>
      ))}
    </dl>
  );
}

// 信頼の根拠：計測の仕組み・数字の定義・限界・作業範囲・流れ・情報の扱いを1ページにまとめる
export default function MethodPage() {
  return (
    <div className="lg:grid lg:grid-cols-[13rem_minmax(0,1fr)] lg:gap-12">
      <aside className="hidden lg:block">
        <nav aria-label="目次" className="sticky top-20 space-y-0.5 text-sm">
          {SECTIONS.map((s) => (
            <a key={s.id} href={`#${s.id}`} className="block rounded-md px-2.5 py-1.5 text-slate-700 hover:bg-slate-100 hover:text-slate-900">
              {s.label}
            </a>
          ))}
        </nav>
      </aside>
      <article className="min-w-0 max-w-3xl">
        <header className="pb-8">
          <p className="text-sm font-semibold text-brand">計測のしかたと進め方</p>
          <h1 className="mt-2 text-[1.875rem] font-bold leading-tight text-slate-900 sm:text-[2.25rem]">
            診断の進め方と、<br />結果の見方。
          </h1>
          <p className="mt-4 leading-relaxed text-slate-700">AIでの紹介状況を調べ、直す場所と内容を整理します。ここでは、調べ方・数字の意味・対応できる範囲をご説明します。</p>
          <nav aria-label="このページの内容" className="mt-5 flex flex-wrap gap-2 lg:hidden">{SECTIONS.map(s => <a key={s.id} href={`#${s.id}`} className="rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">{s.label}</a>)}</nav>
        </header>

        <div className="pb-10">
          <MethodSteps />
          <p className="mt-3 text-sm text-slate-600">くわしい仕組み・数字の定義・限界は、この下にまとめています。</p>
        </div>

        <Block id="measure" title="計測のしかた">
          <ol className="list-decimal space-y-2 pl-5">
            <li>サービスのカテゴリ・対象・強みから、見込み客がAIに聞きそうな質問を作ります。サービス名は質問に入れません（名前を知らない人の質問にするため）。</li>
            <li>ChatGPT（無料チェック）、またはChatGPTとGemini（トライアル・継続支援）に、Web検索を有効にしたAPIで1問ずつ聞きます。質問どうしの会話の続きにはしません。</li>
            <li>回答の本文と、AIが根拠として示したURLをすべて保存します。</li>
            <li>回答の中にサービス名・別の表記・サイトのURLがあれば「紹介された」と数え、箇条書きの何番目かを順位とします。参考URLに公式サイトだけがある場合も「言及」として数えます。</li>
            <li>あわせて公式サイトを読み取り、AIが読めるか、何のサービスか伝わるか、情報の中身と新しさを確認します。</li>
          </ol>
        </Block>

        <Block id="numbers" title="数字の意味">
          <Rows
            rows={[
              ["紹介率", "AIの回答のうち、サービスが紹介された回答の割合。良し悪しは、同じ質問での競合の紹介率と比べて判断します。"],
              ["比較候補の中の順位", "同じ質問の回答に出たサービスを、紹介された回数で並べたときの順位。"],
              ["自社サイトが引用された割合", "AIが根拠として示したサイトのうち、自社サイトの割合。"],
              ["サイト診断の点数", "各項目を OK＝2点・直すとよい＝1点・直す＝0点 で数え、満点に対する割合を100点満点にしたもの（参考の項目は数えません）。紹介率とは別の指標です。"],
              ["改善案の種類", "「技術的な問題」はサイトを読み取って確かめた事実、「情報不足・要確認」は書き足す前に事実の確認が必要なもの、「改善の仮説」は効果が確かでないものです。"],
            ]}
          />
        </Block>

        <Block id="limits" title="限界">
          <ul className="list-disc space-y-2 pl-5">
            <li>AIの回答は、同じ質問でも毎回変わります。数字は目安で、回数が少ないほどぶれます。</li>
            <li>APIで聞いた結果は、一人ひとりがアプリで見る回答（履歴や地域による違い）とは一致しないことがあります。</li>
            <li>サイトを直したことと、紹介や問い合わせが増えたことの因果関係は、計測だけでは証明できません。直したあとに同じ条件で測り直し、変化を確かめます。</li>
            <li>紹介や問い合わせの増加はお約束できません。</li>
          </ul>
        </Block>

        <Block id="scope" title="サイトをどこまで直すか">
          <Rows
            rows={[
              ["無料チェック", "サイトは直しません。計測と診断の結果をお送りします。"],
              ["トライアル", "既存のページを1ページ改善します。CMSで編集できる範囲で、変更前の内容を保存し、ご承認をいただいてから反映します。新しいページの制作、外部媒体への掲載、大規模な実装は含みません。"],
              ["継続支援", "スタンダード（月100,000円・税別）は既存ページの改善、プレミアム（月200,000円・税別）は新規ページの企画・原稿作成・CMSでの制作も含みます。1サービスを対象に、本数・原稿量・修正回数・実装範囲を見積書で合意します。1か月単位の契約です。"],
              ["書かないこと", "事実と確認できない内容（実際にない求人・機能・実績など）は書きません。"],
            ]}
          />
        </Block>

        <Block id="flow" title="申し込み後の流れ">
          <Rows
            rows={[
              ["無料で診断", "3営業日以内に、診断結果をメールでお送りします。電話での営業はしません。ご連絡は診断とご相談への対応に必要な範囲に限ります。"],
              ["トライアル", "2営業日以内に、進め方とお見積もりをメールでお送りします。内容を確認してから正式にご依頼ください。ご依頼後、30分のヒアリングから始めます。"],
            ]}
          />
        </Block>

        <Block id="privacy" title="情報の取り扱い">
          <ul className="list-disc space-y-2 pl-5">
            <li>いただいた会社名・お名前・メールアドレス・サイトのURLは、診断とご連絡にだけ使います。</li>
            <li>サイトのURLは、公開されているページの読み取りと、AIへの質問の結果の判定にだけ使います。ログインが必要なページは読み取りません。</li>
            <li>診断の結果を公開したり、ほかの会社に渡したりすることはありません。事例として紹介する場合は、事前に同意をいただきます。</li>
            <li>データの保管や運営への通知メール配信を、業務委託先のサービスで行う場合があります。通知メールには申し込み者の氏名・メールアドレス・自由記入欄を含めず、会社・サービス名と管理画面へのリンクだけを送ります。</li>
            <li>情報の削除をご希望の場合は、お送りしたメールへの返信、または<Link href="/ai/apply" className="font-semibold text-brand underline">お問い合わせフォーム</Link>でお知らせください。</li>
          </ul>
        </Block>

        <div className="flex flex-wrap gap-3 border-t border-slate-200 pt-8">
          <Link href="/ai/apply" className={btn("primary", "lg")}>
            無料で診断する
          </Link>
          <Link href="/ai/sample" className={btn("secondary", "lg")}>
            サンプルレポートを見る
          </Link>
        </div>
      </article>
    </div>
  );
}
