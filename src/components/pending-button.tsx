"use client";

import { useFormStatus } from "react-dom";

export function PendingButton({ children, pendingLabel }: { children: React.ReactNode; pendingLabel: string }) {
  const { pending } = useFormStatus();
  return (
    <button
      type="submit"
      disabled={pending}
      className="rounded-md px-2 py-1 text-xs font-semibold text-brand hover:bg-indigo-50 disabled:opacity-60"
    >
      {pending ? pendingLabel : children}
    </button>
  );
}
