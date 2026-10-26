"use client";

import { useState } from "react";

// 書き換え例やコードをコピーする。コピーできない環境では、文字を選択状態にする
export function CopyButton({ text, targetId }: { text: string; targetId: string }) {
  const [copied, setCopied] = useState(false);
  return (
    <button
      type="button"
      onClick={async () => {
        try {
          await navigator.clipboard.writeText(text);
          setCopied(true);
          setTimeout(() => setCopied(false), 2000);
        } catch {
          const el = document.getElementById(targetId);
          if (el) window.getSelection()?.selectAllChildren(el);
        }
      }}
      className="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-sm font-semibold text-slate-800 hover:bg-slate-50 print:hidden"
    >
      {copied ? "コピーしました" : "コピー"}
    </button>
  );
}
