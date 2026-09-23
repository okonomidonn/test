import { hash } from "bcryptjs";
import { PrismaPg } from "@prisma/adapter-pg";
import { PrismaClient, type Platform } from "../src/generated/prisma/client";

const adapter = new PrismaPg({ connectionString: process.env.DATABASE_URL });
const prisma = new PrismaClient({ adapter });

const DAY = 24 * 60 * 60 * 1000;

// Deterministic pseudo-random so re-seeding yields stable demo numbers.
let seed = 42;
function rand() {
  seed = (seed * 1103515245 + 12345) % 2 ** 31;
  return seed / 2 ** 31;
}
const between = (min: number, max: number) => Math.floor(min + rand() * (max - min));

const CLIENTS: {
  name: string;
  industry: string;
  contactName: string;
  accounts: { platform: Platform; handle: string; followers: number }[];
  topics: string[];
}[] = [
  {
    name: "カフェ・ド・モカ",
    industry: "飲食",
    contactName: "山田",
    accounts: [
      { platform: "INSTAGRAM", handle: "cafe_de_moka", followers: 12800 },
      { platform: "X", handle: "cafedemoka", followers: 4300 },
    ],
    topics: ["秋限定のマロンラテが登場🌰", "本日のおすすめスイーツ", "週末は10時オープンです", "新しいブレンド豆入荷しました"],
  },
  {
    name: "株式会社グリーンフィット",
    industry: "フィットネス",
    contactName: "佐藤",
    accounts: [
      { platform: "TIKTOK", handle: "greenfit_gym", followers: 25600 },
      { platform: "INSTAGRAM", handle: "greenfit.official", followers: 9100 },
      { platform: "YOUTUBE", handle: "GreenFitChannel", followers: 3200 },
    ],
    topics: ["30秒でできる肩こり解消ストレッチ", "トレーナー紹介シリーズ", "入会キャンペーン実施中", "正しいスクワットのフォーム"],
  },
];

async function main() {
  const passwordHash = await hash("password123", 10);
  const user = await prisma.user.upsert({
    where: { email: "demo@example.com" },
    update: {},
    create: { email: "demo@example.com", name: "デモ 運用担当", passwordHash },
  });

  if ((await prisma.client.count()) > 0) {
    console.log("Clients already exist; skipping demo data.");
    return;
  }

  const now = Date.now();
  for (const c of CLIENTS) {
    const client = await prisma.client.create({
      data: { name: c.name, industry: c.industry, contactName: c.contactName },
    });
    for (const a of c.accounts) {
      const account = await prisma.socialAccount.create({ data: { ...a, clientId: client.id } });

      for (let i = 0; i < 12; i++) {
        const publishedAt = new Date(now - between(1, 60) * DAY - between(0, 12) * 3600_000);
        const impressions = Math.round(a.followers * (0.3 + rand() * 1.5));
        await prisma.post.create({
          data: {
            accountId: account.id,
            authorId: user.id,
            content: `${c.topics[i % c.topics.length]} #${c.name}`,
            status: "PUBLISHED",
            scheduledAt: publishedAt,
            publishedAt,
            impressions,
            likes: Math.round(impressions * (0.02 + rand() * 0.06)),
            comments: Math.round(impressions * rand() * 0.006),
            shares: Math.round(impressions * rand() * 0.01),
            saves: Math.round(impressions * rand() * 0.012),
          },
        });
      }

      for (let i = 1; i <= 2; i++) {
        await prisma.post.create({
          data: {
            accountId: account.id,
            authorId: user.id,
            content: `【予約】${c.topics[i]} #${c.name}`,
            status: "SCHEDULED",
            scheduledAt: new Date(now + i * 2 * DAY),
          },
        });
      }
      await prisma.post.create({
        data: { accountId: account.id, authorId: user.id, content: `下書き: ${c.topics[0]}`, status: "DRAFT" },
      });
    }
  }

  console.log("Seed completed. Demo login: demo@example.com / password123");
}

main()
  .catch((e) => {
    console.error(e);
    process.exitCode = 1;
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
