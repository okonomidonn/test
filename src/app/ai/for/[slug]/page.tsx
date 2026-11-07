import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { JobsLanding } from "@/components/ai/jobs-landing";

// AIススメルは求人サイト・人材サービス専門。/ai/for/jobs はトップと同じ中身を出す（営業メールで案内したURLを残すため）
export function generateStaticParams() {
  return [{ slug: "jobs" }];
}

export const metadata: Metadata = {
  // トップと同じ中身なので、検索エンジンにはトップを正とする
  alternates: { canonical: "/ai" },
  title: "求人は、AIで探す時代へ。求人サイトのAI検索対策 | AIススメル",
  description: "求人サイト・人材サービス専門のAI検索対策。ChatGPT・Geminiで求職者や採用担当者に紹介されているかを調べ、直し方までお伝えします。",
};

export default async function VerticalPage({ params }: PageProps<"/ai/for/[slug]">) {
  if ((await params).slug !== "jobs") notFound();
  return <JobsLanding />;
}
