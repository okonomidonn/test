import Link from "next/link";
import { redirect } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { effectivePlan } from "@/lib/billing/plans";
import { billingEnabled } from "@/lib/billing/stripe";
import { openBillingPortal, startCheckout } from "@/app/actions/billing";
import { PlanTable } from "@/components/ai/plan-table";
import { cardClass, primaryButtonClass, secondaryButtonClass } from "@/components/ui";

const STATUS_LABEL: Record<string, string> = {
  active: "有効",
  trialing: "お試し期間",
  past_due: "お支払いが確認できていません",
  unpaid: "お支払いが確認できていません",
  canceled: "解約済み",
  incomplete: "お支払い手続き中",
  incomplete_expired: "お支払い手続きが完了しませんでした",
  paused: "一時停止中",
};

const MESSAGES: Record<string, { tone: "ok" | "warn"; text: string }> = {
  success: { tone: "ok", text: "お申し込みありがとうございます。反映まで数十秒かかることがあります（反映されない場合はページを再読み込みしてください）。" },
  canceled: { tone: "warn", text: "お申し込みを中止しました。" },
  already: { tone: "warn", text: "すでに有料プランをご契約中です。プランの変更は「契約を管理する」から行えます。" },
  disabled: { tone: "warn", text: "現在、オンラインでのお申し込みを準備中です。" },
  checkout: { tone: "warn", text: "決済画面を開けませんでした。時間をおいてお試しください。" },
};

const fmtDate = (d: Date) => new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", dateStyle: "long" }).format(d);

export default async function BillingPage({ searchParams }: PageProps<"/ai/app/billing">) {
  const session = await requireAccount();
  if (session.role !== "OWNER") redirect("/ai/app");
  const user = await prisma.user.findUniqueOrThrow({ where: { id: session.id } });
  const plan = effectivePlan(user);
  const sp = await searchParams;
  const key = ["success", "canceled", "error"].find((k) => sp[k]);
  const message = key === "error" ? MESSAGES[String(sp.error)] : key ? MESSAGES[key] : undefined;
  const enabled = billingEnabled();
  const hasSubscription = !!user.stripeSubscriptionId;

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold">料金プラン・契約</h1>
        <p className="mt-1 text-sm text-slate-600">有料プランはいつでも解約でき、契約期間の縛りはありません。</p>
      </div>

      {message && (
        <p className={`rounded-lg px-4 py-3 text-sm ${message.tone === "ok" ? "bg-emerald-50 text-emerald-800" : "bg-amber-50 text-amber-800"}`}>{message.text}</p>
      )}

      <section className={`${cardClass} flex flex-wrap items-center justify-between gap-4`}>
        <div>
          <p className="text-sm text-slate-600">現在のプラン</p>
          <p className="text-2xl font-bold text-slate-900">{plan.name}</p>
          {user.subscriptionStatus && <p className="text-sm text-slate-700">状態：{STATUS_LABEL[user.subscriptionStatus] ?? user.subscriptionStatus}</p>}
          {user.currentPeriodEnd && hasSubscription && (
            <p className="text-sm text-slate-700">
              {user.cancelAtPeriodEnd ? `${fmtDate(user.currentPeriodEnd)}で解約されます` : `次回の更新日：${fmtDate(user.currentPeriodEnd)}`}
            </p>
          )}
          {user.subscriptionStatus === "past_due" && (
            <p className="mt-1 text-sm text-rose-700">カードの有効期限などをご確認ください。お支払いが確認できない状態が続くと、フリープランに戻ります。</p>
          )}
        </div>
        {user.stripeCustomerId && enabled && (
          <form action={openBillingPortal}>
            <button type="submit" className={secondaryButtonClass}>
              契約を管理する（プラン変更・カード変更・解約・領収書）
            </button>
          </form>
        )}
      </section>

      <PlanTable
        current={plan.key}
        action={(p) => {
          if (p.key === "FREE" || p.key === plan.key) return null;
          if (hasSubscription) {
            return <p className="text-center text-sm text-slate-600">プランの変更は「契約を管理する」から</p>;
          }
          return (
            <form action={startCheckout.bind(null, p.key)}>
              <button type="submit" disabled={!enabled} className={`${primaryButtonClass} w-full`}>
                {enabled ? `${p.name}プランに申し込む` : "準備中"}
              </button>
            </form>
          );
        }}
      />

      <p className="text-sm text-slate-600">
        お支払いはStripe（クレジットカード）で安全に処理されます。カード情報は当サービスには保存されません。{" "}
        <Link href="/ai/legal" className="underline">
          特定商取引法に基づく表記
        </Link>
      </p>
    </div>
  );
}
