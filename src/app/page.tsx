import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { SearchForm } from "@/components/search-form";
import { JobCard } from "@/components/job-card";
import { CATEGORIES } from "@/lib/constants";

export default async function Home() {
  const featuredJobs = await prisma.job.findMany({
    where: { isPublished: true },
    orderBy: { createdAt: "desc" },
    take: 6,
    include: { company: { select: { name: true } } },
  });

  const jobCount = await prisma.job.count({ where: { isPublished: true } });

  return (
    <div>
      <section
        className="px-10 py-15 text-center"
        style={{
          background: "linear-gradient(135deg, rgba(189, 245, 229, 0.4), rgba(221, 213, 255, 0.3))",
        }}
      >
        <div className="mx-auto max-w-2xl">
          <h1 className="text-4xl font-bold text-text-dark leading-relaxed md:text-5xl">
            好きな時間に、好きな場所で、仕事をする。
          </h1>
          <p className="mt-5 text-base text-text-light leading-relaxed">
            授業の合間に。実家から。カフェで。あなたのペースで稼げる在宅ワークが見つかる。
          </p>
          <div className="mt-8 flex flex-wrap gap-3 justify-center">
            <Link
              href="/jobs"
              className="inline-block rounded-lg px-9 py-3.5 font-semibold text-white transition-all hover:translate-y-[-2px]"
              style={{ background: "var(--aqua)" }}
            >
              求人を探す
            </Link>
            <Link
              href="/register"
              className="inline-block rounded-lg px-9 py-3.5 font-semibold border-2 transition-all hover:translate-y-[-2px]"
              style={{
                borderColor: "var(--aqua)",
                color: "var(--aqua)",
              }}
            >
              今すぐ登録
            </Link>
          </div>
        </div>
      </section>

      <section className="px-10 py-15 bg-white">
        <h2 className="text-center text-3xl font-bold text-text-dark mb-12 md:text-4xl">
          zaito が選ばれる理由
        </h2>
        <div className="mx-auto max-w-5xl grid grid-cols-1 gap-6 md:grid-cols-3">
          <div
            className="p-8 rounded-2xl border-l-4"
            style={{
              backgroundColor: "var(--bg-light)",
              borderLeftColor: "var(--aqua)",
            }}
          >
            <div className="text-4xl mb-3">🕐</div>
            <h3 className="text-lg font-semibold text-text-dark mb-2">自由な時間</h3>
            <p className="text-sm text-text-light leading-relaxed">
              1日1時間からOK。授業やサークルと両立できる案件が豊富。スキマ時間で稼げます。
            </p>
          </div>

          <div
            className="p-8 rounded-2xl border-l-4"
            style={{
              backgroundColor: "var(--bg-light)",
              borderLeftColor: "var(--pink)",
            }}
          >
            <div className="text-4xl mb-3">💡</div>
            <h3 className="text-lg font-semibold text-text-dark mb-2">実務スキル</h3>
            <p className="text-sm text-text-light leading-relaxed">
              ライティング、デザイン、プログラミング。就活に役立つスキルが身につく。
            </p>
          </div>

          <div
            className="p-8 rounded-2xl border-l-4"
            style={{
              backgroundColor: "var(--bg-light)",
              borderLeftColor: "var(--peach)",
            }}
          >
            <div className="text-4xl mb-3">🌍</div>
            <h3 className="text-lg font-semibold text-text-dark mb-2">場所を選ばない</h3>
            <p className="text-sm text-text-light leading-relaxed">
              自宅、図書館、カフェ。どこからでも仕事ができます。
            </p>
          </div>
        </div>
      </section>

      <section
        className="px-10 py-15"
        style={{
          background: "linear-gradient(135deg, rgba(37, 200, 176, 0.1), rgba(255, 111, 159, 0.1))",
        }}
      >
        <div className="mx-auto max-w-5xl grid grid-cols-2 gap-8 text-center md:grid-cols-4">
          <div>
            <div className="text-4xl font-bold text-aqua mb-2">3,500+</div>
            <p className="text-sm text-text-light">掲載求人数</p>
          </div>
          <div>
            <div className="text-4xl font-bold text-aqua mb-2">15,000+</div>
            <p className="text-sm text-text-light">登録ユーザー</p>
          </div>
          <div>
            <div className="text-4xl font-bold text-aqua mb-2">¥5〜¥200万</div>
            <p className="text-sm text-text-light">案件単価幅</p>
          </div>
          <div>
            <div className="text-4xl font-bold text-aqua mb-2">4.8★</div>
            <p className="text-sm text-text-light">ユーザー満足度</p>
          </div>
        </div>
      </section>

      <section className="px-10 py-15 bg-white">
        <h2 className="text-center text-3xl font-bold text-text-dark mb-12 md:text-4xl">今週のおすすめ</h2>

        {featuredJobs.length === 0 ? (
          <p className="mt-8 mx-auto max-w-md rounded-2xl bg-bg-light p-8 text-center text-sm text-text-light">
            まだ求人が掲載されていません。企業の方は
            <Link href="/register" className="mx-1 font-semibold text-aqua hover:underline">
              会員登録
            </Link>
            して求人を掲載してみましょう。
          </p>
        ) : (
          <div className="mx-auto max-w-5xl grid grid-cols-1 gap-5 md:grid-cols-3">
            {featuredJobs.map((job) => (
              <Link
                key={job.id}
                href={`/jobs/${job.id}`}
                className="p-6 rounded-2xl border transition-all hover:translate-y-[-2px] hover:shadow-md"
                style={{
                  backgroundColor: "var(--bg-light)",
                  borderColor: "var(--border)",
                }}
              >
                <div className="text-base font-semibold text-text-dark">{job.title}</div>
                <div className="text-sm text-text-light mt-1">{job.company.name}</div>
                <div className="flex flex-wrap gap-2 mt-4">
                  <span
                    className="text-xs font-medium px-2.5 py-1 rounded"
                    style={{
                      backgroundColor: "white",
                      color: "var(--aqua)",
                      border: "1px solid var(--aqua)",
                    }}
                  >
                    在宅
                  </span>
                  {job.salary && (
                    <span
                      className="text-xs font-medium px-2.5 py-1 rounded"
                      style={{
                        backgroundColor: "white",
                        color: "var(--aqua)",
                        border: "1px solid var(--aqua)",
                      }}
                    >
                      {job.salary}
                    </span>
                  )}
                </div>
              </Link>
            ))}
          </div>
        )}

        {featuredJobs.length > 0 && (
          <div className="text-center mt-10">
            <Link
              href="/jobs"
              className="inline-block text-sm font-semibold text-aqua hover:underline"
            >
              すべて見る →
            </Link>
          </div>
        )}
      </section>

      <section
        className="px-10 py-15 text-center"
        style={{
          background: "linear-gradient(135deg, var(--mint), var(--lavender))",
        }}
      >
        <h2 className="text-3xl font-bold text-text-dark mb-4 md:text-4xl">始めるなら、今。</h2>
        <p className="text-base text-text-light mb-8 max-w-md mx-auto">
          登録は無料。1分で完了します。
        </p>
        <Link
          href="/register"
          className="inline-block rounded-lg px-9 py-3.5 font-semibold text-white transition-all hover:translate-y-[-2px]"
          style={{ background: "var(--aqua)" }}
        >
          無料で始める →
        </Link>
      </section>
    </div>
  );
}
