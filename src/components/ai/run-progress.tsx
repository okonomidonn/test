"use client";

import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";

// 計測画面を開いている間、サーバーに続きの処理を頼み、進み具合を表示する
export function RunProgress({ runId, storeId, initialDone, total }: { runId: string; storeId: string; initialDone: number; total: number }) {
  const router = useRouter();
  const [done, setDone] = useState(initialDone);
  const [error, setError] = useState<string | null>(null);
  const stopped = useRef(false);

  useEffect(() => {
    stopped.current = false;
    (async () => {
      while (!stopped.current) {
        try {
          const res = await fetch(`/api/ai/runs/${runId}/step`, { method: "POST" });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const data: { status: string; done: number } = await res.json();
          setDone(data.done);
          setError(null);
          if (data.status !== "RUNNING") {
            // 終わったら、計測結果の画面へ戻る
            router.push(`/ai/app/stores/${storeId}`);
            break;
          }
          router.refresh();
        } catch {
          setError("通信が途切れました。自動で再開します…");
          await new Promise((r) => setTimeout(r, 5000));
        }
      }
    })();
    return () => {
      stopped.current = true;
    };
  }, [runId, storeId, router]);

  const ratio = total ? done / total : 0;
  return (
    <div className="space-y-2">
      <div className="flex items-center justify-between text-sm">
        <span className="font-semibold text-slate-800">AIに質問中… {done} / {total}</span>
        <span className="text-slate-600">{Math.round(ratio * 100)}%</span>
      </div>
      <div className="h-2 overflow-hidden rounded-full bg-slate-100">
        <div className="h-2 rounded-full bg-brand transition-all" style={{ width: `${ratio * 100}%` }} />
      </div>
      <p className="text-sm text-slate-600">このページを開いたままにすると早く終わります。閉じても、残りは自動で続きます（時間がかかる場合があります）。</p>
      {error && <p className="text-sm text-amber-700">{error}</p>}
    </div>
  );
}
