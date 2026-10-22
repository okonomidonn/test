import Link from "next/link";
import { createStore } from "@/app/actions/ai-check";
import { StoreForm, WebServiceForm } from "@/components/ai/forms";
import { cardClass } from "@/components/ui";
import { requireAccount } from "@/lib/session";

export default async function NewStorePage({ searchParams }: PageProps<"/ai/app/new">) {
  await requireAccount();
  const isWeb = (await searchParams).kind === "web";
  const tab = (active: boolean) =>
    `rounded-lg px-4 py-2 text-sm font-semibold ${active ? "bg-brand text-white" : "bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50"}`;

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{isWeb ? "Webサービスを登録" : "お店を登録"}</h1>
        <p className="mt-1 text-sm text-slate-500">
          {isWeb ? "カテゴリ・対象・強みから" : "エリアと業態から"}、AIに聞く質問を自動で作ります（あとで変更できます）。
        </p>
      </div>
      <div className="flex gap-2">
        <Link href="/ai/app/new" className={tab(!isWeb)}>
          飲食店
        </Link>
        <Link href="/ai/app/new?kind=web" className={tab(isWeb)}>
          Webサービス
        </Link>
      </div>
      <div className={cardClass}>
        {isWeb ? <WebServiceForm action={createStore} submitLabel="登録して質問を作る" /> : <StoreForm action={createStore} submitLabel="登録して質問を作る" />}
      </div>
    </div>
  );
}
