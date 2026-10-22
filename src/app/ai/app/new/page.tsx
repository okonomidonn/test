import Link from "next/link";
import { createStore } from "@/app/actions/ai-check";
import { StoreForm, WebServiceForm } from "@/components/ai/forms";
import { cardClass } from "@/components/ui";
import { requireAccount } from "@/lib/session";

const KINDS = [
  { key: "restaurant", href: "/ai/app/new", title: "お店", body: "飲食店など、場所のあるお店" },
  { key: "web", href: "/ai/app/new?kind=web", title: "Webサービス", body: "求人サイト・SaaS・予約サイトなど" },
] as const;

export default async function NewStorePage({ searchParams }: PageProps<"/ai/app/new">) {
  await requireAccount();
  const isWeb = (await searchParams).kind === "web";

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <h1 className="text-2xl font-bold">登録する</h1>
        <p className="mt-1 text-slate-700">何を登録するかを選んでください。入力した内容から、AIに聞く質問を自動で作ります（あとで変更できます）。</p>
      </div>
      <div className="grid grid-cols-2 gap-3">
        {KINDS.map((k) => {
          const active = (k.key === "web") === isWeb;
          return (
            <Link
              key={k.key}
              href={k.href}
              aria-current={active ? "page" : undefined}
              className={`rounded-xl border-2 p-4 ${active ? "border-brand bg-indigo-50" : "border-slate-200 bg-white hover:border-slate-300"}`}
            >
              <span className={`block text-lg font-bold ${active ? "text-brand" : "text-slate-900"}`}>{k.title}</span>
              <span className="mt-0.5 block text-sm text-slate-700">{k.body}</span>
            </Link>
          );
        })}
      </div>
      <div className={cardClass}>
        {isWeb ? <WebServiceForm action={createStore} submitLabel="登録して質問を作る" /> : <StoreForm action={createStore} submitLabel="登録して質問を作る" />}
      </div>
    </div>
  );
}
