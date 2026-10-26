import { redirect } from "next/navigation";

// 新規の登録は受け付けていない（セルフ版の販売終了）。無料診断の申し込みへ案内する
export default function AiSignupPage() {
  redirect("/ai/apply");
}
