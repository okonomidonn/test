"use client";

import { useActionState, useState } from "react";
import { applyToJob, type ApplyState } from "@/app/actions/applications";

type EducationEntry = { school: string; faculty: string; graduationDate: string };
type WorkExperienceEntry = { company: string; period: string; description: string };

const emptyEducation: EducationEntry = { school: "", faculty: "", graduationDate: "" };
const emptyWorkExperience: WorkExperienceEntry = { company: "", period: "", description: "" };

const inputClass =
  "mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100";

export function ApplyForm({ jobId }: { jobId: string }) {
  const applyAction = applyToJob.bind(null, jobId);
  const [state, formAction, isPending] = useActionState<ApplyState, FormData>(applyAction, {});

  const [education, setEducation] = useState<EducationEntry[]>([{ ...emptyEducation }]);
  const [workExperience, setWorkExperience] = useState<WorkExperienceEntry[]>([{ ...emptyWorkExperience }]);

  if (state.success) {
    return (
      <div className="rounded-2xl bg-emerald-50 p-5 text-sm text-brand-dark">
        応募が完了しました!企業からの連絡をお待ちください。
      </div>
    );
  }

  return (
    <form action={formAction} className="space-y-8">
      <h2 className="text-lg font-bold text-slate-900">応募フォーム</h2>

      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}

      <input type="hidden" name="educationJson" value={JSON.stringify(education)} />
      <input type="hidden" name="workExperienceJson" value={JSON.stringify(workExperience)} />

      <section>
        <h3 className="text-sm font-bold text-slate-700">基本情報</h3>
        <div className="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label className="block text-sm font-medium text-slate-700">氏名</label>
            <input type="text" name="fullName" required maxLength={50} placeholder="山田 太郎" className={inputClass} />
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700">フリガナ(任意)</label>
            <input type="text" name="fullNameKana" maxLength={50} placeholder="ヤマダ タロウ" className={inputClass} />
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700">生年月日(任意)</label>
            <input type="date" name="birthDate" className={inputClass} />
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700">電話番号(任意)</label>
            <input type="tel" name="phone" maxLength={20} placeholder="090-1234-5678" className={inputClass} />
          </div>
        </div>
      </section>

      <section>
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-bold text-slate-700">学歴(任意)</h3>
          <button
            type="button"
            onClick={() => setEducation((prev) => [...prev, { ...emptyEducation }])}
            className="text-xs font-semibold text-brand-dark hover:underline"
          >
            + 学歴を追加
          </button>
        </div>
        <div className="mt-3 space-y-3">
          {education.map((entry, index) => (
            <div key={index} className="grid grid-cols-1 gap-2 rounded-xl border border-slate-100 p-3 sm:grid-cols-[1fr_1fr_auto_auto]">
              <input
                type="text"
                placeholder="学校名"
                value={entry.school}
                onChange={(e) =>
                  setEducation((prev) =>
                    prev.map((it, i) => (i === index ? { ...it, school: e.target.value } : it)),
                  )
                }
                className={inputClass}
              />
              <input
                type="text"
                placeholder="学部・学科(任意)"
                value={entry.faculty}
                onChange={(e) =>
                  setEducation((prev) =>
                    prev.map((it, i) => (i === index ? { ...it, faculty: e.target.value } : it)),
                  )
                }
                className={inputClass}
              />
              <input
                type="text"
                placeholder="卒業年月(例: 2020年3月)"
                value={entry.graduationDate}
                onChange={(e) =>
                  setEducation((prev) =>
                    prev.map((it, i) => (i === index ? { ...it, graduationDate: e.target.value } : it)),
                  )
                }
                className={inputClass}
              />
              {education.length > 1 && (
                <button
                  type="button"
                  onClick={() => setEducation((prev) => prev.filter((_, i) => i !== index))}
                  className="mt-1 h-fit self-center rounded-full px-3 py-2 text-xs font-medium text-rose-500 hover:bg-rose-50 sm:mt-0"
                >
                  削除
                </button>
              )}
            </div>
          ))}
        </div>
      </section>

      <section>
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-bold text-slate-700">職務経歴(任意)</h3>
          <button
            type="button"
            onClick={() => setWorkExperience((prev) => [...prev, { ...emptyWorkExperience }])}
            className="text-xs font-semibold text-brand-dark hover:underline"
          >
            + 職歴を追加
          </button>
        </div>
        <div className="mt-3 space-y-3">
          {workExperience.map((entry, index) => (
            <div key={index} className="space-y-2 rounded-xl border border-slate-100 p-3">
              <div className="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_1fr_auto]">
                <input
                  type="text"
                  placeholder="会社名"
                  value={entry.company}
                  onChange={(e) =>
                    setWorkExperience((prev) =>
                      prev.map((it, i) => (i === index ? { ...it, company: e.target.value } : it)),
                    )
                  }
                  className={inputClass}
                />
                <input
                  type="text"
                  placeholder="在籍期間(例: 2018年4月〜2022年3月)"
                  value={entry.period}
                  onChange={(e) =>
                    setWorkExperience((prev) =>
                      prev.map((it, i) => (i === index ? { ...it, period: e.target.value } : it)),
                    )
                  }
                  className={inputClass}
                />
                {workExperience.length > 1 && (
                  <button
                    type="button"
                    onClick={() => setWorkExperience((prev) => prev.filter((_, i) => i !== index))}
                    className="mt-1 h-fit self-center rounded-full px-3 py-2 text-xs font-medium text-rose-500 hover:bg-rose-50 sm:mt-0"
                  >
                    削除
                  </button>
                )}
              </div>
              <textarea
                placeholder="業務内容(任意)"
                rows={2}
                value={entry.description}
                onChange={(e) =>
                  setWorkExperience((prev) =>
                    prev.map((it, i) => (i === index ? { ...it, description: e.target.value } : it)),
                  )
                }
                className={inputClass}
              />
            </div>
          ))}
        </div>
      </section>

      <section>
        <label className="block text-sm font-bold text-slate-700">志望動機</label>
        <textarea
          name="motivation"
          required
          rows={4}
          maxLength={2000}
          placeholder="この求人に応募した理由を教えてください"
          className={`${inputClass} mt-2`}
        />
      </section>

      <section>
        <label className="block text-sm font-bold text-slate-700">自己PR</label>
        <textarea
          name="coverLetter"
          required
          rows={5}
          maxLength={3000}
          placeholder="スキルや実績、アピールしたい点を教えてください"
          className={`${inputClass} mt-2`}
        />
      </section>

      <button
        type="submit"
        disabled={isPending}
        className="w-full rounded-full bg-brand px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60"
      >
        {isPending ? "送信中..." : "この求人に応募する"}
      </button>
    </form>
  );
}
