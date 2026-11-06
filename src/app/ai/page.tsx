import type { Metadata } from "next";
import { JobsLanding } from "@/components/ai/jobs-landing";

export const metadata: Metadata = {
  title: "AIススメル | 求人サイトのAI検索対策",
  description: "求人サイト・人材サービス専門のAI検索対策。ChatGPT・Geminiで求職者や採用担当者に紹介されているかを実際に聞いて数え、どこを直せば紹介されるかまでお伝えします。",
};

export default function AiLandingPage() {
  return <JobsLanding />;
}
