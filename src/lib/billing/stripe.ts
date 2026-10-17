import Stripe from "stripe";
import type { PaidPlan } from "./plans";

let client: Stripe | null = null;

export function billingEnabled(): boolean {
  return !!(process.env.STRIPE_SECRET_KEY && process.env.STRIPE_PRICE_STARTER && process.env.STRIPE_PRICE_PRO);
}

export function stripe(): Stripe {
  const key = process.env.STRIPE_SECRET_KEY;
  if (!key) throw new Error("STRIPE_SECRET_KEY is not set");
  client ??= new Stripe(key);
  return client;
}

export function priceIdFor(plan: PaidPlan): string {
  const id = plan === "STARTER" ? process.env.STRIPE_PRICE_STARTER : process.env.STRIPE_PRICE_PRO;
  if (!id) throw new Error(`STRIPE_PRICE_${plan} is not set`);
  return id;
}

export function planForPrice(priceId: string | undefined): PaidPlan | null {
  if (!priceId) return null;
  if (priceId === process.env.STRIPE_PRICE_STARTER) return "STARTER";
  if (priceId === process.env.STRIPE_PRICE_PRO) return "PRO";
  return null;
}
