"use client";

import { useState, useTransition } from "react";
import { updateApplicationStatus } from "@/app/actions/applications";
import { APPLICATION_STATUS_LABELS } from "@/lib/constants";

const SELECTABLE_STATUSES = ["REVIEWING", "OFFERED", "REJECTED"] as const;

export function ApplicationStatusSelect({
  applicationId,
  status,
}: {
  applicationId: string;
  status: string;
}) {
  const [value, setValue] = useState(status);
  const [isPending, startTransition] = useTransition();

  return (
    <select
      value={value}
      disabled={isPending}
      onChange={(e) => {
        const next = e.target.value as (typeof SELECTABLE_STATUSES)[number];
        setValue(next);
        startTransition(() => {
          updateApplicationStatus(applicationId, next);
        });
      }}
      className="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 outline-none focus:border-brand disabled:opacity-60"
    >
      <option value="SUBMITTED" disabled>
        {APPLICATION_STATUS_LABELS.SUBMITTED}
      </option>
      {SELECTABLE_STATUSES.map((s) => (
        <option key={s} value={s}>
          {APPLICATION_STATUS_LABELS[s]}
        </option>
      ))}
    </select>
  );
}
