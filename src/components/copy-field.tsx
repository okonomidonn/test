"use client";

import { useState } from "react";

export function CopyField({ value }: { value: string }) {
  const [copied, setCopied] = useState(false);
  return (
    <div className="flex gap-2">
      <input
        readOnly
        value={value}
        onFocus={(e) => e.target.select()}
        className="flex-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 font-mono text-xs"
      />
      <button
        type="button"
        onClick={async () => {
          await navigator.clipboard.writeText(value);
          setCopied(true);
          setTimeout(() => setCopied(false), 1500);
        }}
        className="rounded-md border border-slate-200 px-3 py-1 text-xs hover:bg-slate-50"
      >
        {copied ? "コピーしました" : "コピー"}
      </button>
    </div>
  );
}
