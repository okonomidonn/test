import Link from "next/link";
import { notFound } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { ApplicationStatusSelect } from "@/components/application-status-select";

type EducationEntry = { school: string; faculty?: string; graduationDate?: string };
type WorkExperienceEntry = { company: string; period?: string; description?: string };

function asEducationList(value: unknown): EducationEntry[] {
  return Array.isArray(value) ? (value as EducationEntry[]) : [];
}

function asWorkExperienceList(value: unknown): WorkExperienceEntry[] {
  return Array.isArray(value) ? (value as WorkExperienceEntry[]) : [];
}

export default async function JobApplicationsPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const session = await auth();
  const userId = session!.user.id;

  const job = await prisma.job.findUnique({
    where: { id },
    include: {
      company: true,
      applications: {
        orderBy: { createdAt: "desc" },
        include: { seeker: { select: { name: true, email: true } } },
      },
    },
  });

  if (!job || job.company.userId !== userId) {
    notFound();
  }

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6">
      <Link href="/company" className="text-sm font-medium text-slate-500 hover:text-brand-dark">
        &larr; ダッシュボードに戻る
      </Link>

      <h1 className="mt-3 text-2xl font-black text-slate-900">{job.title}への応募者</h1>
      <p className="mt-1 text-sm text-slate-500">{job.applications.length}件の応募</p>

      {job.applications.length === 0 ? (
        <p className="mt-8 rounded-2xl bg-slate-50 p-8 text-center text-sm text-slate-500">
          まだ応募はありません。
        </p>
      ) : (
        <ul className="mt-6 space-y-4">
          {job.applications.map((application) => {
            const education = asEducationList(application.education);
            const workExperience = asWorkExperienceList(application.workExperience);

            return (
              <li key={application.id} className="rounded-2xl border border-slate-100 p-5">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p className="font-bold text-slate-900">
                      {application.fullName}
                      {application.fullNameKana && (
                        <span className="ml-2 text-xs font-normal text-slate-400">
                          ({application.fullNameKana})
                        </span>
                      )}
                    </p>
                    <p className="text-xs text-slate-400">{application.seeker.email}</p>
                    <p className="mt-0.5 text-xs text-slate-400">
                      {application.phone && <>電話: {application.phone} / </>}
                      {application.birthDate && (
                        <>生年月日: {application.birthDate.toLocaleDateString("ja-JP")} / </>
                      )}
                      応募日: {application.createdAt.toLocaleDateString("ja-JP")}
                    </p>
                  </div>
                  <ApplicationStatusSelect applicationId={application.id} status={application.status} />
                </div>

                {education.length > 0 && (
                  <div className="mt-4">
                    <p className="text-xs font-bold text-slate-500">学歴</p>
                    <ul className="mt-1 space-y-1">
                      {education.map((entry, i) => (
                        <li key={i} className="text-sm text-slate-700">
                          {entry.graduationDate && (
                            <span className="text-slate-400">{entry.graduationDate}　</span>
                          )}
                          {entry.school}
                          {entry.faculty && ` ${entry.faculty}`}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}

                {workExperience.length > 0 && (
                  <div className="mt-4">
                    <p className="text-xs font-bold text-slate-500">職務経歴</p>
                    <ul className="mt-1 space-y-2">
                      {workExperience.map((entry, i) => (
                        <li key={i} className="text-sm text-slate-700">
                          <p className="font-medium">
                            {entry.company}
                            {entry.period && (
                              <span className="ml-2 text-xs font-normal text-slate-400">
                                {entry.period}
                              </span>
                            )}
                          </p>
                          {entry.description && (
                            <p className="mt-0.5 whitespace-pre-wrap text-slate-500">{entry.description}</p>
                          )}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}

                <div className="mt-4">
                  <p className="text-xs font-bold text-slate-500">志望動機</p>
                  <p className="mt-1 whitespace-pre-wrap rounded-xl bg-slate-50 p-3 text-sm text-slate-700">
                    {application.motivation}
                  </p>
                </div>

                <div className="mt-3">
                  <p className="text-xs font-bold text-slate-500">自己PR</p>
                  <p className="mt-1 whitespace-pre-wrap rounded-xl bg-slate-50 p-3 text-sm text-slate-700">
                    {application.coverLetter}
                  </p>
                </div>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}
