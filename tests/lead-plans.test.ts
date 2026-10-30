import test from "node:test";
import assert from "node:assert/strict";
import { LEAD_PLANS, leadLabel, leadMessage, resolveLeadPlan } from "../src/lib/leads/plans";

test("月額の希望プランは本文が空でも保存・一覧表示まで失われない", () => {
  for (const plan of ["basic", "production"] as const) {
    const selected = resolveLeadPlan(plan);
    const stored = leadMessage(selected);
    assert.equal(leadLabel(LEAD_PLANS[selected].kind, stored), LEAD_PLANS[plan].label);
    assert.equal(leadLabel("TRIAL", leadMessage(selected, "予算と対象範囲を相談したい")), LEAD_PLANS[plan].label);
    assert.ok(leadMessage(selected, "相談本文")?.endsWith("\n相談本文"));
  }
});

test("以前のフォーム・既存データと不正なクエリ値は月額に誤分類しない", () => {
  for (const invalid of [undefined, null, "toString", "__proto__", ["basic"], "unknown"]) {
    assert.equal(resolveLeadPlan(invalid), "check");
    assert.equal(resolveLeadPlan(invalid, "TRIAL"), "trial");
  }
  assert.equal(leadMessage("trial", "相談"), "相談");
  assert.equal(leadMessage("check"), undefined);
  assert.equal(leadLabel("TRIAL", "以前の相談"), "トライアル");
  assert.equal(leadLabel("CHECK", "【制作プランのご相談】"), "無料チェック");
});
