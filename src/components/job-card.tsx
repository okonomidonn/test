import Link from "next/link";
import {
  EMPLOYMENT_TYPE_LABELS,
  WORK_STYLE_LABELS,
  formatSalary,
} from "@/lib/constants";

export type JobCardData = {
  id: string;
  title: string;
  category: string;
  employmentType: string;
  workStyle: string;
  location: string | null;
  salaryMin: number | null;
  salaryMax: number | null;
  tags: string;
  company: { name: string };
};

export function JobCard({ job }: { job: JobCardData }) {
  const tagList = job.tags
    .split(",")
    .map((t) => t.trim())
    .filter(Boolean);

  return (
    <Link
      href={`/jobs/${job.id}`}
      className="block rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md"
    >
      <div className="flex flex-wrap items-center gap-2">
        <span className="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-brand-dark">
          {WORK_STYLE_LABELS[job.workStyle] ?? job.workStyle}
        </span>
        <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
          {job.category}
        </span>
        <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
          {EMPLOYMENT_TYPE_LABELS[job.employmentType] ?? job.employmentType}
        </span>
      </div>

      <h3 className="mt-3 text-lg font-bold text-slate-900">{job.title}</h3>
      <p className="mt-1 text-sm text-slate-500">{job.company.name}</p>

      <div className="mt-4 flex flex-wrap items-center justify-between gap-2">
        <p className="text-base font-bold text-brand-dark">
          {formatSalary(job.salaryMin, job.salaryMax)}
        </p>
        {job.location && <p className="text-xs text-slate-400">{job.location}</p>}
      </div>

      {tagList.length > 0 && (
        <div className="mt-3 flex flex-wrap gap-1.5">
          {tagList.slice(0, 5).map((tag) => (
            <span key={tag} className="rounded bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
              #{tag}
            </span>
          ))}
        </div>
      )}
    </Link>
  );
}
