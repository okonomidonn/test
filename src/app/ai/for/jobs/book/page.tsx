import Image from "next/image";
import { BookForm } from "@/components/ai/book-form";
import { Band, Faq, Head } from "@/components/ai/lp";

export const metadata = {
  title: "求人サイトのAI集客はじめてBOOK｜無料ダウンロード | AIススメル",
  description: "求職者がChatGPTで求人を探す時代に、AIに紹介される求人サイトになるための無料ガイド。仕組み・選ばれるサイトの共通点・実践チェックリスト付き。",
};

const PDF = "/ai/jobs-ai-book.pdf";

const LEARN = ["AIが求人サイトを紹介する仕組み", "AIに紹介されやすいサイト・されにくいサイトの共通点", "トップページの「最初の一文」の書き方", "今日から使える12項目の実践チェックリスト", "ChatGPTで自社の紹介状況を確かめる方法"];
const BAD = ["トップページが求人の検索窓と一覧だけで、サイトの説明文がない", "「在宅」「未経験OK」などの強みが、絞り込みボタンにしか書かれていない", "大手の求人サイトと同じ言葉で説明していて、違いが伝わらない"];
const FAQ: [string, string][] = [
  ["AIの知識がなくても読めますか？", "はい。専門用語をできるだけ使わず、求人サイトの運営者が「何から始めればよいか」を中心にまとめています。"],
  ["どんな求人サイト向けですか？", "職種・地域・働き方などに特化した求人サイトや、求人メディアを運営している方に向けた内容です。"],
  ["ダウンロードしたら営業の電話がかかってきますか？", "電話での営業はしません。資料に関するご案内を、メールで1〜2回お送りすることがあります。"],
];

export default function JobsBookPage() {
  return (
    <div className="-mt-8">
      <section className="ml-[calc(50%-50vw)] w-screen bg-white">
        <div className="mx-auto grid max-w-6xl gap-10 px-4 pb-14 pt-10 sm:px-6 sm:pt-16 lg:grid-cols-[1.15fr_1fr] lg:gap-14">
          <div>
            <p className="text-sm text-slate-600">求人サイト運営者のための無料ガイド</p>
            <h1 className="mt-3 text-[2rem] font-bold leading-[1.35] text-slate-900 sm:text-[2.75rem]">
              求人サイトの
              <br />
              AI集客はじめてBOOK
            </h1>
            <p className="mt-6 max-w-lg leading-loose text-slate-700">求職者がChatGPTで求人を探す時代に、「AIに紹介される求人サイト」になるための対策を7ページにまとめました。実践チェックリスト付きです。</p>
            <div className="mt-7 flex items-end gap-6">
              <Image src="/ai/jobs-ai-book-cover.jpg" alt="求人サイトのAI集客はじめてBOOKの表紙" width={662} height={936} className="w-40 rounded-lg ring-1 ring-slate-300 sm:w-48" />
              <p className="text-sm leading-relaxed text-slate-600">PDF・全7ページ
                <br />
                実践チェックリスト付き</p>
            </div>
          </div>
          <div id="download" className="rounded-lg border border-slate-300 bg-white p-5 sm:p-7">
            <p className="text-lg font-bold text-slate-900">無料でダウンロード</p>
            <p className="mt-1 mb-5 text-sm text-slate-600">入力したメールアドレスに、資料をお送りします</p>
            <BookForm pdf={PDF} />
          </div>
        </div>
      </section>

      <Band className="bg-slate-50">
        <Head>求職者は、AIに相談しながら仕事を探しはじめています</Head>
        <div className="mt-10 grid gap-10 md:grid-cols-2">
          <div className="border-t-2 border-slate-900 pt-5">
            <p className="font-num text-5xl font-bold text-slate-900">46.5%</p>
            <p className="mt-2 font-bold text-slate-900">20代の転職希望者のうち、就職・転職活動で生成AIを利用している人</p>
            <p className="mt-3 text-xs leading-relaxed text-slate-600">「よく利用」19.8%＋「時々利用」26.7%。出典：株式会社学情「20代転職希望者の生成AI活用に関する調査」（2025年8月2日〜18日、20代専門転職サイト「Re就活」来訪者、n=303）</p>
          </div>
          <div className="border-t-2 border-slate-900 pt-5">
            <p className="font-num text-5xl font-bold text-slate-900">84.5%</p>
            <p className="mt-2 font-bold text-slate-900">生成AIを「継続して利用している」大学生。利用サービスはChatGPTが90.4%</p>
            <p className="mt-3 text-xs leading-relaxed text-slate-600">出典：産業能率大学コンテンツビジネス研究所「学生メディア・コンテンツ利用調査2026 −生成AI編−」（2026年4月7日〜7月3日、同大学の在学生、n=2,607）</p>
          </div>
        </div>
        <p className="mt-10 max-w-3xl leading-loose text-slate-700">若い世代ほどAIを日常的に使っています。「どこで仕事を探せばいいか」をAIに聞く求職者が増えるほど、AIの回答に名前が出るかどうかで差がつきます。</p>
      </Band>

      <Band>
        <Head>AIに紹介されにくい求人サイトの共通点</Head>
        <ul className="mt-8 max-w-3xl list-disc space-y-2 pl-5 leading-relaxed text-slate-800 marker:text-slate-400">
          {BAD.map((b) => (
            <li key={b}>{b}</li>
          ))}
        </ul>
        <p className="mt-6 font-bold text-slate-900">では、何をどう直せばよいのか。資料で具体的に解説しています。</p>
      </Band>

      <Band className="bg-slate-50">
        <Head>このBOOKで分かること</Head>
        <ul className="mt-8 max-w-3xl list-disc space-y-2 pl-5 leading-relaxed text-slate-800 marker:text-slate-400">
          {LEARN.map((l) => (
            <li key={l}>{l}</li>
          ))}
        </ul>
        <div className="mt-10">
          <a href="#download" className="inline-flex min-h-13 w-full items-center justify-center rounded-lg bg-brand px-7 font-bold text-white hover:bg-brand-dark sm:w-auto">
            無料でダウンロードする
          </a>
          <p className="mt-2 text-sm text-slate-600">※30秒で入力完了</p>
        </div>
      </Band>

      <Band>
        <Head>よくある質問</Head>
        <Faq items={FAQ} />
      </Band>
    </div>
  );
}
