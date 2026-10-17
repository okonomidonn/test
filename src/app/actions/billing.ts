"use server";

import { headers } from "next/headers";
import { redirect } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { PAID_PLANS, effectivePlan, type PaidPlan } from "@/lib/billing/plans";
import { billingEnabled, priceIdFor, stripe } from "@/lib/billing/stripe";

async function baseUrl() {
  if (process.env.APP_URL) return process.env.APP_URL.replace(/\/$/, "");
  const h = await headers();
  const host = h.get("x-forwarded-host") ?? h.get("host");
  const proto = h.get("x-forwarded-proto") ?? "https";
  return `${proto}://${host}`;
}

async function requireOwner() {
  const session = await requireAccount();
  if (session.role !== "OWNER") redirect("/ai/app");
  return prisma.user.findUniqueOrThrow({ where: { id: session.id } });
}

async function ensureCustomer(user: { id: string; email: string; name: string; stripeCustomerId: string | null }) {
  if (user.stripeCustomerId) return user.stripeCustomerId;
  const customer = await stripe().customers.create(
    { email: user.email, name: user.name, metadata: { userId: user.id } },
    // 二重送信で顧客が2つ作られないようにする
    { idempotencyKey: `customer-${user.id}` },
  );
  await prisma.user.update({ where: { id: user.id }, data: { stripeCustomerId: customer.id } });
  return customer.id;
}

// 有料プランの申し込み（Stripeの決済画面へ移動する）
export async function startCheckout(plan: PaidPlan) {
  if (!PAID_PLANS.includes(plan)) redirect("/ai/app/billing");
  if (!billingEnabled()) redirect("/ai/app/billing?error=disabled");
  const user = await requireOwner();

  // 既に契約中なら、プラン変更・解約は契約管理画面で行う（二重契約を防ぐ）
  if (effectivePlan(user).key !== "FREE" || user.stripeSubscriptionId) {
    redirect("/ai/app/billing?error=already");
  }

  const base = await baseUrl();
  let url: string | null = null;
  try {
    const customer = await ensureCustomer(user);
    const session = await stripe().checkout.sessions.create({
      mode: "subscription",
      customer,
      client_reference_id: user.id,
      line_items: [{ price: priceIdFor(plan), quantity: 1 }],
      subscription_data: { metadata: { userId: user.id } },
      allow_promotion_codes: true,
      locale: "ja",
      success_url: `${base}/ai/app/billing?success=1`,
      cancel_url: `${base}/ai/app/billing?canceled=1`,
    });
    url = session.url;
  } catch (e) {
    console.error("stripe checkout failed", e);
  }
  // redirect() は例外で画面遷移するため、try の外で呼ぶ
  redirect(url ?? "/ai/app/billing?error=checkout");
}

// プラン変更・カード変更・解約・領収書の確認（Stripeの契約管理画面へ移動する）
export async function openBillingPortal() {
  if (!billingEnabled()) redirect("/ai/app/billing?error=disabled");
  const user = await requireOwner();
  if (!user.stripeCustomerId) redirect("/ai/app/billing");
  const returnUrl = `${await baseUrl()}/ai/app/billing`;
  let url: string | null = null;
  try {
    const session = await stripe().billingPortal.sessions.create({ customer: user.stripeCustomerId, locale: "ja", return_url: returnUrl });
    url = session.url;
  } catch (e) {
    console.error("stripe portal failed", e);
  }
  redirect(url ?? "/ai/app/billing?error=checkout");
}
