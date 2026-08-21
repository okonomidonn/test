import { hash } from "bcryptjs";
import { PrismaBetterSqlite3 } from "@prisma/adapter-better-sqlite3";
import { PrismaClient } from "../src/generated/prisma/client";

const adapter = new PrismaBetterSqlite3({
  url: process.env.DATABASE_URL ?? "file:./dev.db",
});
const prisma = new PrismaClient({ adapter });

async function main() {
  const passwordHash = await hash("password123", 10);

  const companiesData = [
    {
      email: "hr@cloudworks.example.com",
      name: "採用担当 田中",
      companyName: "株式会社クラウドワークス風",
      description:
        "フルリモートで働くエンジニア・デザイナーが集まるIT企業です。全国どこからでも働けます。",
      website: "https://example.com/cloudworks",
      jobs: [
        {
          title: "【フルリモート】フロントエンドエンジニア募集",
          description:
            "React/Next.jsを用いた自社SaaSプロダクトの開発をお任せします。\n出社は不要で、コアタイムなしのフルフレックスです。\n\n【必須スキル】\n・React/TypeScriptでの開発経験2年以上\n\n【歓迎スキル】\n・Next.jsでの開発経験\n・デザインシステムの構築経験",
          category: "エンジニア・IT",
          employmentType: "FULL_TIME",
          workStyle: "FULL_REMOTE",
          location: "全国どこでも可",
          salaryMin: 5000000,
          salaryMax: 8000000,
          tags: "React,TypeScript,Next.js,フルフレックス",
        },
        {
          title: "【業務委託】バックエンドエンジニア(Node.js)",
          description:
            "APIサーバーの設計・開発・運用をお任せします。週2〜3日から相談可能です。\n\n【必須スキル】\n・Node.jsでの開発経験\n・RDBの設計経験",
          category: "エンジニア・IT",
          employmentType: "CONTRACT",
          workStyle: "FULL_REMOTE",
          location: null,
          salaryMin: 600000,
          salaryMax: 900000,
          tags: "Node.js,週2日〜,業務委託",
        },
      ],
    },
    {
      email: "recruit@homeletter.example.com",
      name: "採用担当 佐藤",
      companyName: "ホームレター編集部",
      description: "在宅ライター・編集者向けのメディア運営会社です。未経験者の育成にも力を入れています。",
      website: "https://example.com/homeletter",
      jobs: [
        {
          title: "【未経験歓迎】在宅Webライター",
          description:
            "ライフスタイル系メディアの記事執筆をお任せします。\n1文字1.5円〜、月20本前後からスタートできます。\n\n未経験の方には研修動画をご用意しています。",
          category: "ライティング・編集",
          employmentType: "FREELANCE",
          workStyle: "FULL_REMOTE",
          location: null,
          salaryMin: null,
          salaryMax: null,
          tags: "未経験可,ライター,主婦・主夫歓迎",
        },
        {
          title: "在宅アルバイト カスタマーサポート(チャット対応)",
          description:
            "ECサイトのチャットサポート業務です。1日3時間〜、シフト自由。\nパソコンとネット環境があればOKです。",
          category: "カスタマーサポート",
          employmentType: "PART_TIME",
          workStyle: "FULL_REMOTE",
          location: null,
          salaryMin: 1200,
          salaryMax: 1500,
          tags: "シフト自由,主婦・主夫歓迎,1日3時間〜",
        },
      ],
    },
    {
      email: "jobs@pixeldesign.example.com",
      name: "採用担当 鈴木",
      companyName: "ピクセルデザイン合同会社",
      description: "地方拠点のデザイン会社です。首都圏メンバーはほぼ全員リモート勤務です。",
      website: null,
      jobs: [
        {
          title: "【ハイブリッド】UI/UXデザイナー",
          description:
            "toB向けSaaSプロダクトのUI/UXデザインをお任せします。\n月1〜2回の出社(東京オフィス)があります。\n\nFigmaでのデザイン経験がある方を歓迎します。",
          category: "デザイン",
          employmentType: "FULL_TIME",
          workStyle: "HYBRID",
          location: "東京都(月1〜2回出社)",
          salaryMin: 4500000,
          salaryMax: 7000000,
          tags: "Figma,UI/UX,月1出社",
        },
      ],
    },
  ];

  for (const companyData of companiesData) {
    const user = await prisma.user.upsert({
      where: { email: companyData.email },
      update: {},
      create: {
        email: companyData.email,
        name: companyData.name,
        passwordHash,
        role: "COMPANY",
        company: {
          create: {
            name: companyData.companyName,
            description: companyData.description,
            website: companyData.website,
          },
        },
      },
      include: { company: true },
    });

    const companyId = user.company!.id;

    for (const job of companyData.jobs) {
      const existing = await prisma.job.findFirst({
        where: { companyId, title: job.title },
      });
      if (!existing) {
        await prisma.job.create({
          data: {
            ...job,
            employmentType: job.employmentType as "FULL_TIME" | "PART_TIME" | "CONTRACT" | "FREELANCE" | "INTERNSHIP",
            workStyle: job.workStyle as "FULL_REMOTE" | "HYBRID" | "ON_SITE",
            companyId,
          },
        });
      }
    }
  }

  await prisma.user.upsert({
    where: { email: "seeker@example.com" },
    update: {},
    create: {
      email: "seeker@example.com",
      name: "デモ 求職者",
      passwordHash,
      role: "SEEKER",
    },
  });

  console.log("Seed completed.");
  console.log("Demo accounts (password: password123):");
  console.log("  Seeker : seeker@example.com");
  console.log("  Company: hr@cloudworks.example.com");
}

main()
  .catch((e) => {
    console.error(e);
    process.exitCode = 1;
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
