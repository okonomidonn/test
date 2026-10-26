import { redirect } from "next/navigation";

// セルフ版ツールの販売は終了（以前のリンクのために残し、料金ページへ転送する）
export default function ToolPage() {
  redirect("/ai/pricing");
}
