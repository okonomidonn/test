import type { MetadataRoute } from "next";

const BASE = "https://www.aisusumeru.com";

// AIススメルの公開ページ（/ai/for/jobs はトップと同じ中身なので載せない）
export default function sitemap(): MetadataRoute.Sitemap {
  const now = new Date();
  return [
    { url: `${BASE}/ai`, lastModified: now, changeFrequency: "weekly", priority: 1 },
    { url: `${BASE}/ai/pricing`, lastModified: now, changeFrequency: "monthly", priority: 0.8 },
    { url: `${BASE}/ai/sample`, lastModified: now, changeFrequency: "monthly", priority: 0.6 },
    { url: `${BASE}/ai/method`, lastModified: now, changeFrequency: "monthly", priority: 0.6 },
    { url: `${BASE}/ai/for/jobs/book`, lastModified: now, changeFrequency: "monthly", priority: 0.4 },
    { url: `${BASE}/ai/legal`, lastModified: now, changeFrequency: "yearly", priority: 0.2 },
    { url: `${BASE}/ai/privacy`, lastModified: now, changeFrequency: "yearly", priority: 0.2 },
  ];
}
