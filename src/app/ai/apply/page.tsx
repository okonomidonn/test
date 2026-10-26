import Link from "next/link";
import { auth } from "@/auth";
import { sampleReport } from "@/lib/ai-check/sample";
import { todoOf } from "@/components/ai/report-view";
import { prisma } from "@/lib/prisma";
import { LeadForm } from "@/components/ai/lead-form";
import { cardClass } from "@/components/ui";

export const metadata = { title: "申し込み | ススメル" };

const COPY = {
  CHECK: {
    title: "無料チェックを申し込む",
    lead: "見込み客がAIに聞きそうな質問で、御社のサービスがChatGPTに紹介されているかを計測し、公式サイトを診断して結果をお送りします。",
    points: ["料金：0円", "結果：直すところ上位3つと、代わりに紹介されている競合", "お届け：3営業日以内にメールで"],
  },
  TRIAL: {
    title: "GEO診断・改善トライアルを申し込む",
    lead: "現状を数字で確かめ、改善を1つ実際に行い、30日後に同じ条件で測り直します。",
    points: ["料金：49,800円（税別・1回）", "ChatGPT・Geminiで各10問を計測、競合2社と比較", "直す点を3つ特定し、既存ページを1ページ改善", "30日後に再計測して報告"],
  },
} as const;

export default async function ApplyPage({ searchParams }: PageProps<"/ai/apply">) {
  const sp = await searchParams;
  const kind = sp.plan === "trial" ? "TRIAL" : "CHECK";
  const copy = COPY[kind];
  const sample = sampleReport();
  const rate = sample.measured ? Math.round((sample.measured.hits / sample.measured.total) * 100) : 0;

  // ログイン中なら、登録済みのサービスとアカウントの情報を入れておく
  const session = await auth();
  let defaults: { company?: string; websiteUrl?: string; name?: string; email?: string } = {};
  if (session?.user?.id) {
    const user = await prisma.user.findUnique({ where: { id: session.user.id }, select: { name: true, email: true } });
    const storeId = typeof sp.store === "string" ? sp.store : undefined;
    const store = storeId ? await prisma.store.findFirst({ where: { id: storeId, ownerId: session.user.id }, select: { name: true, websiteUrl: true } }) : null;
    defaults = { name: user?.name, email: user?.email, company: store?.name, websiteUrl: store?.websiteUrl ?? undefined };
  }

  return (
    <div className="mx-auto grid max-w-5xl gap-x-10 gap-y-8 py-4 lg:grid-cols-[1fr_1.2fr]">
      <div className="min-w-0">
        <p className="font-semibold text-brand">{kind === "TRIAL" ? "STEP 2" : "STEP 1"}</p>
        <h1 className="mt-2 text-3xl font-black text-slate-900">{copy.title}</h1>
        <p className="mt-4 text-slate-700">{copy.lead}</p>
        <ul className="mt-5 space-y-2 border-t border-slate-200 pt-5">
          {copy.points.map((p) => (
            <li key={p} className="flex gap-2 text-slate-800">
              <span className="text-brand" aria-hidden>
                ✓
              </span>
              {p}
            </li>
          ))}
        </ul>
      </div>
      <div className={`${cardClass} min-w-0 lg:col-start-2 lg:row-span-2 lg:row-start-1 lg:self-start`}>
        <LeadForm kind={kind} defaults={defaults} />
      </div>
      {/* スマホでは、申し込みフォームのあとに例と流れを出す */}
      <div className="min-w-0 lg:col-start-1">
        <div className="rounded-xl border border-slate-200 bg-white p-5">
          <p className="text-sm font-bold text-slate-900">{kind === "TRIAL" ? "お渡しする報告書の例" : "お届けする結果の例"}</p>
          <dl className="mt-3 grid grid-cols-3 gap-3 border-y border-slate-200 py-3">
            {[
              ["紹介された割合", `${rate}%`],
              ["サイト診断", `${sample.score}点`],
              ["直すところ", `${todoOf(sample.checks).length}項目`],
            ].map(([k, v]) => (
              <div key={k}>
                <dt className="text-xs font-semibold text-slate-600">{k}</dt>
                <dd className="font-num text-2xl font-semibold text-slate-900">{v}</dd>
              </div>
            ))}
          </dl>
          <p className="mt-3 text-sm text-slate-700">直すところごとに、直す場所・今の内容・書き換え例が付きます。</p>
          <Link href="/ai/sample" className="mt-2 inline-block text-sm font-semibold text-brand hover:underline">
            サンプルの報告書を見る（架空のサービス） →
          </Link>
        </div>

        <h2 className="mt-8 text-lg font-bold text-slate-900">送信したあと</h2>
        <ol className="mt-3 space-y-2 text-slate-800">
          {(kind === "TRIAL"
            ? ["2営業日以内に、進め方とお見積もりをメールでお送りします", "内容を確認してから正式にご依頼ください。申し込みの時点では料金はかかりません", "ご依頼後、30分のヒアリングから始めます"]
            : ["3営業日以内に、計測と診断の結果をメールでお送りします", "電話での営業はしません。トライアルのご案内をメールで1回お送りすることがあります", "続けるかどうかは、結果を見てから決めてください"]
          ).map((t, i) => (
            <li key={t} className="grid grid-cols-[1.5rem_1fr] gap-1">
              <span className="font-num font-semibold text-slate-500">{i + 1}</span>
              <span>{t}</span>
            </li>
          ))}
        </ol>
        <p className="mt-6 text-sm text-slate-600">
          いただいた情報は、診断とご連絡にだけ使います。結果を公開したり、ほかの会社に渡したりすることはありません。AIの回答は毎回変わるため、紹介や問い合わせの増加をお約束するものではありません。
        </p>
      </div>
    </div>
  );
}
