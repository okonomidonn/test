import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "AI検索チェック for Webサービス | ChatGPTからの集客",
  description: "ChatGPT・GeminiでWebサービスがどう紹介されているかを計測し、紹介されやすいサイトの作りを診断します。",
};

const RESULTS = [
  { k: "ChatGPT経由のユーザー", v: "14.5%", note: "469人中68人" },
  { k: "成果（興味あり・応募など）に占める割合", v: "51.6%", note: "95件中49件" },
  { k: "1人あたりの成果", v: "約3.6倍", note: "サイト全体の平均と比べて" },
];

const CHECKS = [
  { title: "細かい質問で計測", body: "「おすすめの〇〇は？」だけでなく、「〇〇で△△できるサービスは？」のような、強みごとの細かい質問を自動で作り、ChatGPT・Geminiに聞きます。" },
  { title: "URLだけの紹介も数える", body: "AIはサービス名を出さず、リンクだけで紹介することがあります。公式サイトが参考ページに入っていれば、紹介されたと数えます。" },
  { title: "競合が引用されているページ", body: "AIが紹介の根拠にしたページ（比較記事・レビュー・競合の機能ページなど）を一覧にします。何を用意すればいいかがわかります。" },
  { title: "AIに読まれる作りかを診断", body: "一文の立ち位置、強みの言葉、1件1ページの情報、構造化データ、llms.txt、robots.txt、サイトマップを確認し、直し方を提案します。" },
];

export default function AiWebLandingPage() {
  return (
    <div className="space-y-20 pb-12">
      <section className="pt-6">
        <p className="text-sm font-semibold text-brand">Webサービスのための AI検索チェック</p>
        <h1 className="mt-3 max-w-3xl text-4xl font-bold leading-tight text-slate-900 sm:text-5xl lg:text-[2.75rem]">
          あなたのサービス、
          <br />
          ChatGPTに紹介されていますか？
        </h1>
        <p className="mt-5 max-w-2xl text-base leading-relaxed text-slate-600">
          「〇〇できるサービスは？」とAIに聞いて、紹介されたサイトをそのまま使う人が増えています。
          Googleで上位に出ない新しいサービスでも、AIには紹介されることがあります。いまの紹介状況と、何を直せばいいかを無料で確かめられます。
        </p>
        <div className="mt-8 flex flex-wrap gap-3">
          <Link href="/ai/signup?kind=web" className="rounded-lg bg-brand px-6 py-3 font-semibold text-white shadow-sm hover:bg-brand-dark">
            無料でサービスを診断する
          </Link>
          <Link href="/ai/login" className="rounded-lg border border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 hover:bg-slate-50">
            ログイン
          </Link>
        </div>
        <p className="mt-3 text-xs text-slate-500">フリープランはカード登録不要。有料プランは月4,980円から、いつでも解約できます。</p>
      </section>

      <section className="rounded-2xl bg-white p-8 ring-1 ring-slate-200">
        <h2 className="text-2xl font-bold text-slate-900">事例：在宅ワークの求人サイト「zaito」（運営元の自社サイト）</h2>
        <p className="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600">
          2026年8月開設。Google検索ではほとんど出てこないサイトですが、求人を1件1ページにし、「完全在宅の仕事だけ」という一文の立ち位置と構造化データを整えたところ、ChatGPTからの訪問が始まりました。
          ChatGPT経由の人が見たのは、主に個別の求人ページ（46ページ）でした。細かい質問に答えを持つページが紹介されています。
        </p>
        <div className="mt-6 grid gap-4 sm:grid-cols-3">
          {RESULTS.map((r) => (
            <div key={r.k} className="rounded-xl bg-slate-50 p-5">
              <p className="text-xs text-slate-500">{r.k}</p>
              <p className="mt-1 text-3xl font-bold text-slate-900">{r.v}</p>
              <p className="text-xs text-slate-500">{r.note}</p>
            </div>
          ))}
        </div>
        <p className="mt-4 text-xs text-slate-400">
          Googleアナリティクス4（2026年9月24日〜10月21日）。ChatGPT経由＝セッションの参照元が chatgpt.com。開設まもないサイト1件・28日間の数字で、広告の開始時期とも重なるため、施策と流入増の因果は確定していません。
        </p>
      </section>

      <section>
        <h2 className="text-2xl font-bold text-slate-900">できること</h2>
        <div className="mt-6 grid gap-4 md:grid-cols-2">
          {CHECKS.map((f) => (
            <div key={f.title} className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h3 className="font-bold text-slate-900">{f.title}</h3>
              <p className="mt-2 text-sm leading-relaxed text-slate-600">{f.body}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="rounded-2xl bg-slate-900 p-10 text-center text-white">
        <h2 className="text-2xl font-bold">まずは1サービス、無料で計測してみませんか</h2>
        <p className="mt-2 text-sm text-slate-300">登録は1分。AIの回答は毎回変わるため、計測結果は目安です。紹介や流入の増加をお約束するものではありません。</p>
        <Link href="/ai/signup?kind=web" className="mt-6 inline-block rounded-lg bg-white px-6 py-3 font-semibold text-slate-900 hover:bg-slate-100">
          無料で始める
        </Link>
      </section>
    </div>
  );
}
