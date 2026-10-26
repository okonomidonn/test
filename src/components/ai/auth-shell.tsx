import type { ReactNode } from "react";

// ログイン・新規登録の共通の枠（左揃えの見出し、入力欄、下に切り替えのリンク）
export function AuthShell({ title, sub, children, footer }: { title: string; sub?: string; children: ReactNode; footer: ReactNode }) {
  return (
    <div className="mx-auto w-full max-w-sm py-8 sm:py-14">
      <h1 className="text-[1.75rem] font-bold text-slate-900">{title}</h1>
      {sub && <p className="mt-2 text-slate-600">{sub}</p>}
      <div className="mt-6 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">{children}</div>
      <p className="mt-5 text-sm text-slate-600">{footer}</p>
    </div>
  );
}
