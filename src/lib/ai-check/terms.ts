// 画面の言葉を、飲食店向けとWebサービス向けで切り替える
export type TargetKind = "RESTAURANT" | "WEB";

export const TERMS = {
  RESTAURANT: {
    target: "お店",
    self: "自店",
    competitor: "比べたいお店",
    frequent: "AIがよく紹介しているお店",
    platform: "グルメサイト・地図",
    sourcesHint: "ここに載っている情報が、AIの紹介文の材料になっています。自店の情報が正しいか確認しましょう。",
    missedHint: "これらの質問に当てはまるお店なら、公式サイトやGoogleマップに「当てはまる」と文章で書くことで、紹介されやすくなる可能性があります。",
  },
  WEB: {
    target: "サービス",
    self: "自社",
    competitor: "比べたい競合サービス",
    frequent: "AIがよく紹介しているサービス",
    platform: "比較・レビュー・SNS",
    sourcesHint: "ここに載っている情報が、AIの紹介文の材料になっています。競合が引用されているページの種類（比較記事・レビュー・事例など）を、自社サイトにも用意しましょう。",
    missedHint:
      "これらの質問に当てはまるなら、答えがぴったり書かれたページ（機能・事例・料金・比較などを1件1ページ）を作るか、既存ページに同じ言葉で書き足すと、紹介されやすくなる可能性があります。",
  },
} as const;
