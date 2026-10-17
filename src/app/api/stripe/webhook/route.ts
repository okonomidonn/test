import type Stripe from "stripe";
import { NextResponse } from "next/server";
import { stripe } from "@/lib/billing/stripe";
import { syncSubscription } from "@/lib/billing/sync";

// Stripeからの通知（契約の開始・変更・解約）を受け取り、ユーザーのプランを更新する
export async function POST(req: Request) {
  const secret = process.env.STRIPE_WEBHOOK_SECRET;
  const signature = req.headers.get("stripe-signature");
  if (!secret || !signature) return NextResponse.json({ error: "not configured" }, { status: 400 });

  let event: Stripe.Event;
  try {
    // 署名の検証には、受け取ったままの本文が必要
    event = await stripe().webhooks.constructEventAsync(await req.text(), signature, secret);
  } catch {
    return NextResponse.json({ error: "invalid signature" }, { status: 400 });
  }

  switch (event.type) {
    case "customer.subscription.created":
    case "customer.subscription.updated":
    case "customer.subscription.deleted":
    case "customer.subscription.paused":
    case "customer.subscription.resumed":
      return NextResponse.json(await syncSubscription(event.data.object, event.created));
    default:
      return NextResponse.json({ ignored: event.type });
  }
}
