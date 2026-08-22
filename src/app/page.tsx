import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { CATEGORIES } from "@/lib/constants";

export default async function Home() {
  const featuredJobs = await prisma.job.findMany({
    where: { isPublished: true },
    orderBy: { createdAt: "desc" },
    take: 3,
    include: { company: { select: { name: true } } },
  });

  return (
    <div style={{ background: "var(--paper)" }}>
      {/* Hero Section */}
      <section
        style={{
          position: "relative",
          overflow: "hidden",
          padding: "58px 0 80px",
        }}
      >
        <div
          style={{
            content: '""',
            position: "absolute",
            borderRadius: "50%",
            zIndex: "-1",
            width: "470px",
            height: "470px",
            background: "var(--lav)",
            right: "-170px",
            top: "-130px",
          }}
        />
        <div
          style={{
            content: '""',
            position: "absolute",
            borderRadius: "50%",
            zIndex: "-1",
            width: "300px",
            height: "300px",
            background: "var(--mint)",
            left: "-170px",
            bottom: "-130px",
          }}
        />

        <div style={{ width: "min(1160px, calc(100% - 40px))", margin: "auto" }}>
          <div
            style={{
              display: "grid",
              gridTemplateColumns: "1.08fr .92fr",
              gap: "50px",
              alignItems: "center",
            }}
          >
            <div>
              <div
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: "8px",
                  background: "#fff",
                  border: "1px solid var(--line)",
                  borderRadius: "999px",
                  padding: "7px 12px",
                  fontSize: "12px",
                  fontWeight: "900",
                  boxShadow: "0 8px 24px rgba(24,32,51,.05)",
                }}
              >
                <span
                  style={{
                    width: "8px",
                    height: "8px",
                    borderRadius: "50%",
                    background: "var(--aqua)",
                  }}
                />
                在宅ワーク専門求人サイト
              </div>

              <h1
                style={{
                  fontSize: "clamp(48px, 6.2vw, 82px)",
                  lineHeight: 1.03,
                  letterSpacing: "-4px",
                  margin: "20px 0 22px",
                  color: "var(--ink)",
                }}
              >
                自分らしく、
                <br />
                <span
                  style={{
                    fontStyle: "normal",
                    display: "inline-block",
                    background: "linear-gradient(100deg, var(--aqua), #54b8ff)",
                    WebkitBackgroundClip: "text",
                    WebkitTextFillColor: "transparent",
                    backgroundClip: "text",
                  }}
                >
                  おうちで働く。
                </span>
              </h1>

              <p
                style={{
                  fontSize: "18px",
                  color: "var(--muted)",
                  maxWidth: "560px",
                  margin: "0 0 30px",
                }}
              >
                働く場所も、時間も、もっと自由に。zaitoは「完全在宅」にこだわって、あなたらしい仕事との出会いをつくります。
              </p>

              <div
                style={{
                  display: "flex",
                  gap: "9px",
                  flexWrap: "wrap",
                  marginTop: "18px",
                }}
              >
                {CATEGORIES.slice(0, 5).map((cat) => (
                  <span
                    key={cat}
                    style={{
                      background: "#fff",
                      border: "1px solid #e5e2da",
                      borderRadius: "999px",
                      padding: "7px 12px",
                      fontSize: "12px",
                      fontWeight: "800",
                      color: "#505767",
                    }}
                  >
                    #{cat}
                  </span>
                ))}
              </div>

              <div
                style={{
                  marginTop: "30px",
                  display: "flex",
                  gap: "12px",
                  flexWrap: "wrap",
                }}
              >
                <Link
                  href="/jobs"
                  style={{
                    background: "var(--aqua)",
                    color: "white",
                    border: "0",
                    borderRadius: "999px",
                    padding: "12px 24px",
                    fontWeight: "800",
                    cursor: "pointer",
                    textDecoration: "none",
                  }}
                >
                  求人を探す
                </Link>
                <Link
                  href="/register"
                  style={{
                    background: "#fff",
                    border: "1px solid #d9d7d0",
                    borderRadius: "999px",
                    padding: "12px 24px",
                    fontWeight: "800",
                    cursor: "pointer",
                    textDecoration: "none",
                    color: "var(--ink)",
                  }}
                >
                  無料で登録
                </Link>
              </div>
            </div>

            <div style={{ minHeight: "430px", position: "relative" }}>
              <div
                style={{
                  position: "absolute",
                  inset: "25px 0 0 35px",
                  background: "#fff",
                  border: "1px solid #e7e4dc",
                  borderRadius: "30px",
                  boxShadow: "0 18px 50px rgba(24,32,51,.10)",
                  padding: "20px",
                  transform: "rotate(2deg)",
                }}
              >
                <div style={{ display: "flex", gap: "6px", marginBottom: "18px" }}>
                  {[1, 2, 3].map((i) => (
                    <div
                      key={i}
                      style={{
                        width: "9px",
                        height: "9px",
                        borderRadius: "50%",
                        background: "#d7d4cc",
                      }}
                    />
                  ))}
                </div>
                <div
                  style={{
                    height: "175px",
                    borderRadius: "22px",
                    background: "linear-gradient(135deg,#e8fff8,#c8f4eb)",
                    position: "relative",
                    overflow: "hidden",
                  }}
                >
                  <div
                    style={{
                      position: "absolute",
                      left: "22px",
                      top: "25px",
                      fontSize: "30px",
                      fontWeight: "900",
                      lineHeight: 1.05,
                    }}
                  >
                    WORK
                    <br />
                    YOUR
                    <br />
                    WAY.
                  </div>
                </div>
                <div
                  style={{
                    height: "45px",
                    borderRadius: "13px",
                    background: "#f7f6f2",
                    marginTop: "14px",
                  }}
                />
                <div
                  style={{
                    height: "45px",
                    borderRadius: "13px",
                    background: "#f7f6f2",
                    marginTop: "14px",
                    width: "72%",
                  }}
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section style={{ padding: "88px 0", background: "#fff" }}>
        <div style={{ width: "min(1160px, calc(100% - 40px))", margin: "auto" }}>
          <div style={{ marginBottom: "28px" }}>
            <div
              style={{
                fontSize: "12px",
                fontWeight: "900",
                letterSpacing: ".12em",
                color: "var(--aqua)",
                textTransform: "uppercase",
                marginBottom: "7px",
              }}
            >
              FEATURES
            </div>
            <h2
              style={{
                fontSize: "38px",
                lineHeight: 1.15,
                letterSpacing: "-1.5px",
                margin: "0",
                color: "var(--ink)",
              }}
            >
              「在宅で働く」を、もっと身近に。
            </h2>
          </div>

          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(3, 1fr)",
              gap: "18px",
            }}
          >
            {[
              {
                num: "01",
                title: "場所に縛られない",
                desc: "通勤ゼロ。自分の好きな場所が仕事場。授業やサークルと両立できます。",
                bgColor: "#f1fffb",
                textColor: "#087f70",
              },
              {
                num: "02",
                title: "はじめやすい仕事",
                desc: "未経験・大学生OKの求人もたくさん。スキルアップできます。",
                bgColor: "#fff3f7",
                textColor: "#d34e79",
              },
              {
                num: "03",
                title: "あなたのペースで",
                desc: "週2日から、スキマ時間からでも。稼ぎたい分稼げます。",
                bgColor: "#fff9df",
                textColor: "#90731b",
              },
            ].map((card, idx) => (
              <div
                key={idx}
                style={{
                  background: "#fff",
                  border: "1px solid #e7e4dc",
                  borderRadius: "24px",
                  boxShadow: "0 10px 28px rgba(24,32,51,.055)",
                  padding: "22px",
                  transition: ".2s",
                  cursor: "pointer",
                }}
              >
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    marginBottom: "20px",
                  }}
                >
                  <span
                    style={{
                      borderRadius: "999px",
                      padding: "6px 9px",
                      fontSize: "11px",
                      fontWeight: "900",
                      background: card.bgColor,
                      color: card.textColor,
                    }}
                  >
                    {idx === 0 ? "完全在宅" : idx === 1 ? "学生歓迎" : "自由な働き方"}
                  </span>
                  <span style={{ fontSize: "14px", fontWeight: "900" }}>{card.num}</span>
                </div>
                <h3
                  style={{
                    fontSize: "20px",
                    fontWeight: "900",
                    margin: "0 0 7px",
                    color: "var(--ink)",
                  }}
                >
                  {card.title}
                </h3>
                <p
                  style={{
                    fontSize: "12px",
                    color: "var(--muted)",
                    margin: "0",
                  }}
                >
                  {card.desc}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Jobs Section */}
      <section style={{ padding: "88px 0", background: "#fff" }}>
        <div style={{ width: "min(1160px, calc(100% - 40px))", margin: "auto" }}>
          <div
            style={{
              display: "flex",
              alignItems: "end",
              justifyContent: "space-between",
              marginBottom: "28px",
            }}
          >
            <div>
              <div
                style={{
                  fontSize: "12px",
                  fontWeight: "900",
                  letterSpacing: ".12em",
                  color: "var(--aqua)",
                  textTransform: "uppercase",
                  marginBottom: "7px",
                }}
              >
                PICK UP
              </div>
              <h2
                style={{
                  fontSize: "38px",
                  lineHeight: 1.15,
                  letterSpacing: "-1.5px",
                  margin: "0",
                  color: "var(--ink)",
                }}
              >
                今、人気の在宅求人。
              </h2>
            </div>
            <Link
              href="/jobs"
              style={{
                fontSize: "13px",
                fontWeight: "900",
                textDecoration: "none",
                color: "var(--ink)",
              }}
            >
              すべて見る →
            </Link>
          </div>

          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(3, 1fr)",
              gap: "18px",
            }}
          >
            {featuredJobs.length === 0 ? (
              <p
                style={{
                  gridColumn: "1 / -1",
                  textAlign: "center",
                  color: "var(--muted)",
                  padding: "40px",
                }}
              >
                求人がまだありません
              </p>
            ) : (
              featuredJobs.map((job) => (
                <Link
                  key={job.id}
                  href={`/jobs/${job.id}`}
                  style={{
                    background: "#fff",
                    border: "1px solid #e7e4dc",
                    borderRadius: "24px",
                    boxShadow: "0 10px 28px rgba(24,32,51,.055)",
                    padding: "22px",
                    transition: ".2s",
                    textDecoration: "none",
                    display: "block",
                  }}
                >
                  <div
                    style={{
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                      marginBottom: "20px",
                    }}
                  >
                    <span
                      style={{
                        borderRadius: "999px",
                        padding: "6px 9px",
                        fontSize: "11px",
                        fontWeight: "900",
                        background: "#f1fffb",
                        color: "#087f70",
                      }}
                    >
                      人気
                    </span>
                  </div>
                  <h3
                    style={{
                      fontSize: "20px",
                      fontWeight: "900",
                      margin: "0 0 7px",
                      color: "var(--ink)",
                    }}
                  >
                    {job.title}
                  </h3>
                  <p
                    style={{
                      fontSize: "12px",
                      color: "var(--muted)",
                      margin: "0 0 18px",
                    }}
                  >
                    {job.company.name}
                  </p>
                  <div
                    style={{
                      fontSize: "25px",
                      fontWeight: "900",
                      margin: "18px 0 12px",
                      color: "var(--ink)",
                    }}
                  >
                    <span style={{ fontSize: "12px", color: "var(--muted)", fontWeight: "700" }}>
                      時給{" "}
                    </span>
                    1,300円〜
                  </div>
                  <div
                    style={{
                      display: "flex",
                      gap: "7px",
                      flexWrap: "wrap",
                      margin: "12px 0 20px",
                    }}
                  >
                    <span
                      style={{
                        background: "#fff",
                        border: "1px solid #e5e2da",
                        borderRadius: "999px",
                        padding: "7px 12px",
                        fontSize: "10px",
                        fontWeight: "800",
                        color: "#505767",
                      }}
                    >
                      #完全在宅
                    </span>
                    <span
                      style={{
                        background: "#fff",
                        border: "1px solid #e5e2da",
                        borderRadius: "999px",
                        padding: "7px 12px",
                        fontSize: "10px",
                        fontWeight: "800",
                        color: "#505767",
                      }}
                    >
                      #未経験OK
                    </span>
                  </div>
                  <div
                    style={{
                      display: "flex",
                      justifyContent: "space-between",
                      borderTop: "1px solid var(--line)",
                      paddingTop: "14px",
                      fontSize: "11px",
                      color: "#687083",
                    }}
                  >
                    <span>週2〜OK</span>
                    <span>大学生歓迎</span>
                  </div>
                </Link>
              ))
            )}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section style={{ padding: "88px 40px" }}>
        <div
          style={{
            width: "min(1160px, calc(100% - 40px))",
            margin: "auto",
            background: "var(--ink)",
            color: "#fff",
            borderRadius: "32px",
            padding: "54px 58px",
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            gap: "30px",
            overflow: "hidden",
            position: "relative",
          }}
        >
          <div
            style={{
              content: '""',
              position: "absolute",
              width: "250px",
              height: "250px",
              borderRadius: "50%",
              background: "var(--pink)",
              right: "-90px",
              top: "-100px",
              opacity: 0.9,
            }}
          />
          <div style={{ position: "relative", zIndex: 1 }}>
            <div
              style={{
                fontSize: "12px",
                fontWeight: "900",
                letterSpacing: ".12em",
                color: "var(--mint)",
                textTransform: "uppercase",
                marginBottom: "7px",
              }}
            >
              zaito
            </div>
            <h2
              style={{
                fontSize: "38px",
                lineHeight: 1.15,
                letterSpacing: "-1.5px",
                margin: "0 0 12px",
              }}
            >
              自分らしく、おうちで働こう。
            </h2>
            <p style={{ fontSize: "16px", color: "#c8ccd5", margin: "0" }}>
              あなたのペースで見つかる、在宅ワーク。
            </p>
          </div>
          <Link
            href="/register"
            style={{
              background: "var(--mint)",
              color: "#123c37",
              border: "0",
              borderRadius: "999px",
              padding: "12px 24px",
              fontWeight: "800",
              cursor: "pointer",
              textDecoration: "none",
              position: "relative",
              zIndex: 2,
              whiteSpace: "nowrap",
            }}
          >
            求人を探してみる →
          </Link>
        </div>
      </section>
    </div>
  );
}
