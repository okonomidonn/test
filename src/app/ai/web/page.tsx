import { redirect } from "next/navigation";

// 紹介ページは /ai に1つにまとめた（以前配ったリンクのために残す）
export default function AiWebLandingPage() {
  redirect("/ai");
}
