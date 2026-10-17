import { createStore } from "@/app/actions/ai-check";
import { StoreForm } from "@/components/ai/forms";
import { cardClass } from "@/components/ui";
import { requireAccount } from "@/lib/session";

export default async function NewStorePage() {
  await requireAccount();
  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <h1 className="text-2xl font-bold">お店を登録</h1>
        <p className="mt-1 text-sm text-slate-500">エリアと業態から、AIに聞く質問を自動で作ります（あとで変更できます）。</p>
      </div>
      <div className={cardClass}>
        <StoreForm action={createStore} submitLabel="登録して質問を作る" />
      </div>
    </div>
  );
}
