import { JobForm } from "@/components/job-form";
import { createJob } from "@/app/actions/jobs";

export default function NewJobPage() {
  return (
    <div className="mx-auto max-w-2xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-black text-slate-900">求人を投稿する</h1>
      <p className="mt-1 text-sm text-slate-500">在宅ワークの求人情報を入力してください。</p>

      <div className="mt-8">
        <JobForm action={createJob} submitLabel="求人を投稿する" />
      </div>
    </div>
  );
}
