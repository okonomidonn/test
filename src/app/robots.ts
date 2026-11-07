import type { MetadataRoute } from "next";

// 検索エンジンとAIのクローラーに、公開ページを読んでもらう（管理画面・APIは除く）
export default function robots(): MetadataRoute.Robots {
  return {
    rules: [{ userAgent: "*", allow: "/", disallow: ["/ai/app", "/dashboard", "/api/"] }],
    sitemap: "https://www.aisusumeru.com/sitemap.xml",
    host: "https://www.aisusumeru.com",
  };
}
