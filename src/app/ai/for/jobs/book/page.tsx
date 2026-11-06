import Image from "next/image";
import { BookForm } from "@/components/ai/book-form";
import { Faq, Lead, Num, Section } from "@/components/ai/lp";

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
      <section className="ml-[calc(50%-50vw)] w-screen border-b border-slate-200 bg-[#F5F8FD]">
        <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[1.1fr_1fr]">
          <div>
            <p className="text-sm font-bold text-brand">求人サイト運営者のための無料ガイド</p>
            <h1 className="mt-4 text-[2.25rem] font-black leading-[1.3] text-slate-900 sm:text-[3rem]">
              求人サイトの
              <br />
              AI集客はじめてBOOK
            </h1>
            <p className="mt-6 max-w-lg leading-loose text-slate-700">求職者がChatGPTで求人を探す時代に、「AIに紹介される求人サイト」になるための対策を7ページにまとめました。実践チェックリスト付きです。</p>
            <div className="mt-8 flex items-end gap-6">
              <Image src="/ai/jobs-ai-book-cover.jpg" alt="求人サイトのAI集客はじめてBOOKの表紙" width={662} height={936} className="w-40 rounded-md border border-slate-200 shadow-[0_16px_40px_rgba(30,60,120,0.16)] sm:w-48" />
              <p className="text-sm leading-relaxed text-slate-600">PDF・全7ページ
                <br />
                実践チェックリスト付き</p>
            </div>
          </div>
          <div id="download" className="rounded-md border border-slate-200 bg-white p-6 shadow-[0_16px_40px_rgba(30,60,120,0.08)] sm:p-8">
            <p className="text-lg font-bold text-slate-900">無料でダウンロード</p>
            <p className="mt-1 mb-5 text-sm text-slate-600">入力したメールアドレスに、資料をお送りします</p>
            <BookForm pdf={PDF} />
          </div>
        </div>
      </section>

      <Section tone="soft">
        <Lead line="若い世代ほど、仕事探しにAIを使っています。" em="20代の求職者の6割超が、転職活動で生成AIを利用" />
        <div className="mx-auto mt-12 grid max-w-5xl gap-8 border-t-2 border-slate-800 pt-8 sm:grid-cols-2">
          <div><Num className="text-5xl text-brand">63.5%</Num><p className="mt-3 font-bold text-slate-900">20代の求職者のうち、就職・転職活動で生成AIを利用（前年は46.5%）</p><p className="mt-2 text-xs leading-relaxed text-slate-500">株式会社学情「20代転職意識調査」（2026年8月、Re就活来訪者、n=186）</p></div>
          <div><Num className="text-5xl text-brand">84.5%</Num><p className="mt-3 font-bold text-slate-900">生成AIを使い続けている大学生（うちChatGPTが90.4%）</p><p className="mt-2 text-xs leading-relaxed text-slate-500">産業能率大学「学生メディア・コンテンツ利用調査2026」（2026年4〜7月、同大学の在学生、n=2,607）</p></div>
        </div>
      </Section>

      <Section>
        <Lead line="こんな求人サイトは、" em="AIに紹介されにくくなっています" />
        <ol className="mx-auto mt-10 max-w-4xl divide-y divide-slate-200 border-y-2 border-t-slate-800 border-b-slate-200">
          {BAD.map((b) => (
            <li key={b} className="py-6"><p className="font-bold leading-relaxed text-slate-900">{b}</p></li>
          ))}
        </ol>
        <p className="mt-6 text-center text-slate-700">では、何をどう直せばよいのか。資料で具体的に解説しています。</p>
      </Section>

      <Section tone="soft">
        <Lead em="このBOOKで分かること" />
        <ol className="mx-auto mt-10 max-w-4xl divide-y divide-slate-200 border-y-2 border-t-slate-800 border-b-slate-200 bg-white px-6">
          {LEARN.map((l) => (
            <li key={l} className="py-5"><p className="font-bold text-slate-900">{l}</p></li>
          ))}
        </ol>
        <div className="mt-10 text-center">
          <a href="#download" className="inline-flex min-h-13 w-full items-center justify-center rounded-md bg-brand px-8 font-bold text-white hover:bg-brand-dark sm:w-auto">無料でダウンロードする</a>
        </div>
      </Section>

      <Section>
        <Lead em="資料について" />
        <Faq items={FAQ} />
      </Section>
    </div>
  );
}
