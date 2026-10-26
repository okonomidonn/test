import { auth } from "@/auth";
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
    <div className="mx-auto grid max-w-5xl gap-10 py-4 lg:grid-cols-[1fr_1.2fr]">
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
        <p className="mt-5 text-sm text-slate-600">AIの回答は毎回変わります。紹介や問い合わせの増加をお約束するものではありません。</p>
      </div>
      <div className={`${cardClass} min-w-0`}>
        <LeadForm kind={kind} defaults={defaults} />
      </div>
    </div>
  );
}
