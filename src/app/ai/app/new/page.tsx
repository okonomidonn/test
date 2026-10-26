import { createStore } from "@/app/actions/ai-check";
import { WebServiceForm } from "@/components/ai/forms";
import { cardClass } from "@/components/ui";
import { requireAccount } from "@/lib/session";

// 新規登録はWebサービスのみ（飲食店向けは終了。登録済みの飲食店は引き続き表示・編集できる）
export default async function NewStorePage() {
  await requireAccount();
  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <h1 className="text-3xl font-black text-slate-900">サービスを登録</h1>
        <p className="mt-2 text-slate-700">カテゴリ・対象・強みから、見込み客がAIに聞きそうな質問を自動で作ります（あとで変更できます）。</p>
      </div>
      <div className={cardClass}>
        <WebServiceForm action={createStore} submitLabel="登録して質問を作る" />
      </div>
    </div>
  );
}
