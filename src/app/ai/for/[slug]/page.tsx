import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";
import { Band, Cta, Faq, Head } from "@/components/ai/lp";
import { ServicePlans } from "@/components/ai/service-plans";
import { VERTICALS, verticalBySlug } from "@/lib/ai-check/verticals";

export function generateStaticParams() {
  return VERTICALS.map((v) => ({ slug: v.slug }));
}

export async function generateMetadata({ params }: PageProps<"/ai/for/[slug]">): Promise<Metadata> {
  const v = verticalBySlug((await params).slug);
  return v ? { title: v.title, description: v.description } : {};
}

// 業種別の紹介ページ。中身は src/lib/ai-check/verticals.ts
export default async function VerticalPage({ params }: PageProps<"/ai/for/[slug]">) {
  const v = verticalBySlug((await params).slug);
  if (!v) notFound();

  return (
    <div className="-mt-8">
      <section className="ml-[calc(50%-50vw)] w-screen bg-white">
        <div className="mx-auto grid max-w-6xl gap-10 px-4 pb-14 pt-10 sm:px-6 sm:pt-16 lg:grid-cols-[1.15fr_1fr] lg:gap-14">
          <div>
            <p className="text-sm text-slate-600">{v.name}のAI検索対策</p>
            <h1 className="mt-3 text-[2rem] font-bold leading-[1.35] text-slate-900 sm:text-[2.75rem]">
              {v.hero.lead}
              <br />
              {v.hero.em}
              {v.hero.tail}
            </h1>
            <p className="mt-6 max-w-lg leading-loose text-slate-700">{v.hero.sub}</p>
          </div>
          <div id="apply" className="rounded-lg border border-slate-300 bg-white p-5 sm:p-7">
            <p className="text-lg font-bold text-slate-900">{v.name}の無料診断</p>
            <p className="mt-1 text-sm text-slate-600">費用0円・登録不要。3営業日以内にメールでお送りします。</p>
            <div className="mt-5">
              {v.slug === "kunitachi" ? <HeroLeadForm urlLabel="お店のURL（Googleマップ・Instagramでも可）" urlPlaceholder="例）https://maps.app.goo.gl/…" /> : <HeroLeadForm />}
            </div>
          </div>
        </div>
      </section>

      <Band className="bg-slate-50">
        <Head sub={`${v.customer}は、サイトを見る前にAIで候補を絞っています。次の3種類の質問で、あなたのサイトが紹介されるかを測ります。`}>
          {v.customer}は、AIに聞いてから{v.name}を選びます
        </Head>
        <dl className="mt-8 divide-y divide-slate-200 border-y border-slate-200">
          {v.questions.map((q) => (
            <div key={q.type} className="grid gap-1 py-5 md:grid-cols-[11rem_18rem_1fr] md:gap-6">
              <dt className="font-bold text-slate-900">{q.type}</dt>
              <dd className="text-slate-900">「{q.text}」</dd>
              <dd className="text-sm leading-relaxed text-slate-600">{q.note}</dd>
            </div>
          ))}
        </dl>
      </Band>

      <Band>
        <Head>診断で見るところ</Head>
        <dl className="mt-10 grid gap-x-10 gap-y-8 md:grid-cols-2">
          {v.checks.map((c) => (
            <div key={c.title}>
              <dt className="font-bold text-slate-900">{c.title}</dt>
              <dd className="mt-2 leading-relaxed text-slate-700">{c.body}</dd>
            </div>
          ))}
        </dl>
        <h3 className="mt-14 text-lg font-bold text-slate-900">{v.name}でよくある「AIに紹介されない」原因</h3>
        <ul className="mt-4 list-disc space-y-2 pl-5 leading-relaxed text-slate-800 marker:text-slate-400">
          {v.mistakes.map((m) => (
            <li key={m}>{m}</li>
          ))}
        </ul>
        <Cta />
        {v.slug === "jobs" && (
          <p className="mt-6 text-sm text-slate-700">
            まずは情報収集から、という方は
            <Link href="/ai/for/jobs/book" className="font-semibold text-brand underline underline-offset-4">
              「求人サイトのAI集客はじめてBOOK」（無料）
            </Link>
            をどうぞ。
          </p>
        )}
      </Band>

      {v.proof && (
        <Band className="bg-slate-50">
          <div className="grid gap-10 md:grid-cols-[1.3fr_1fr]">
            <div>
              <Head>{v.proof.heading ? v.proof.heading.join("") : `${v.name}を運営する私が、自分のサイトで試しています`}</Head>
              <p className="mt-6 max-w-2xl leading-loose text-slate-700">AIススメルの鳥居です。在宅ワークの求人サイト「zaito」を運営していて、AIにどう紹介されているかを測り、直し、また測ることを自分のサイトで続けてきました。</p>
              <p className="mt-6 font-bold text-slate-900">鳥居 潤</p>
            </div>
            <div className="self-end">
              <p className="text-sm font-bold text-slate-900">{v.proof.title}</p>
              <dl className="mt-3 divide-y divide-slate-200 border-y border-slate-200">
                {v.proof.stats.map(([val, k]) => (
                  <div key={k} className="flex items-baseline justify-between gap-4 py-4">
                    <dt className="text-slate-700">{k}</dt>
                    <dd className="font-num text-3xl font-bold text-slate-900">{val}</dd>
                  </div>
                ))}
              </dl>
              <p className="mt-3 text-xs leading-relaxed text-slate-500">{v.proof.note}</p>
            </div>
          </div>
        </Band>
      )}

      <Band>
        <Head sub="無料チェックだけでもご利用いただけます。有料プランへの自動切り替えはありません。">まず無料で。改善の依頼は、結果を見てから。</Head>
        <div className="mt-10">
          <ServicePlans />
        </div>
      </Band>

      <Band className="bg-slate-50">
        <Head>{v.name}のよくある質問</Head>
        <Faq items={v.faq} />
        <p className="mt-8 text-sm">
          <Link href="/ai" className="font-semibold text-brand underline underline-offset-4">
            AIススメルのサービス全体を見る
          </Link>
        </p>
      </Band>
    </div>
  );
}
