"use client";

import { useState } from "react";

// 系列の色（検証済みの2色。系列が増える場合は「その他」にまとめる）
const SERIES_COLORS = ["#2a78d6", "#eb6834"];
const GRID = "#e5e7eb";
const AXIS_TEXT = "#64748b";

export type TrendPoint = { label: string; values: Record<string, number | null> };

// 推薦率（%）の推移。1本の軸・細い線・十字線のツールチップ。
export function TrendChart({ points, series }: { points: TrendPoint[]; series: { key: string; label: string }[] }) {
  const [hover, setHover] = useState<number | null>(null);
  const W = 640;
  const H = 240;
  const pad = { l: 40, r: 16, t: 16, b: 32 };
  const iw = W - pad.l - pad.r;
  const ih = H - pad.t - pad.b;
  const x = (i: number) => pad.l + (points.length === 1 ? iw / 2 : (i / (points.length - 1)) * iw);
  const y = (v: number) => pad.t + ih - (v / 100) * ih;

  if (points.length === 0) return null;

  return (
    <div className="relative">
      {series.length > 1 && (
        <div className="mb-2 flex flex-wrap gap-4 text-sm text-slate-700">
          {series.map((s, i) => (
            <span key={s.key} className="inline-flex items-center gap-1.5">
              <svg width="16" height="4" aria-hidden>
                <line x1="0" y1="2" x2="16" y2="2" stroke={SERIES_COLORS[i]} strokeWidth="2" strokeLinecap="round" />
              </svg>
              {s.label}
            </span>
          ))}
        </div>
      )}
      <svg
        viewBox={`0 0 ${W} ${H}`}
        className="w-full"
        role="img"
        aria-label="推薦率の推移"
        onPointerLeave={() => setHover(null)}
        onPointerMove={(e) => {
          const rect = e.currentTarget.getBoundingClientRect();
          const px = ((e.clientX - rect.left) / rect.width) * W;
          let best = 0;
          points.forEach((_, i) => {
            if (Math.abs(x(i) - px) < Math.abs(x(best) - px)) best = i;
          });
          setHover(best);
        }}
      >
        {[0, 25, 50, 75, 100].map((v) => (
          <g key={v}>
            <line x1={pad.l} x2={W - pad.r} y1={y(v)} y2={y(v)} stroke={GRID} strokeWidth="1" />
            <text x={pad.l - 8} y={y(v) + 4} textAnchor="end" fontSize="11" fill={AXIS_TEXT}>
              {v}%
            </text>
          </g>
        ))}
        {points.map((p, i) => (
          <text key={i} x={x(i)} y={H - 10} textAnchor="middle" fontSize="11" fill={AXIS_TEXT}>
            {points.length <= 8 || i % Math.ceil(points.length / 8) === 0 ? p.label : ""}
          </text>
        ))}
        {hover !== null && <line x1={x(hover)} x2={x(hover)} y1={pad.t} y2={pad.t + ih} stroke="#94a3b8" strokeWidth="1" />}
        {series.map((s, si) => {
          const pts = points.map((p, i) => ({ i, v: p.values[s.key] })).filter((p): p is { i: number; v: number } => p.v != null);
          return (
            <g key={s.key}>
              {pts.length > 1 && (
                <polyline
                  points={pts.map((p) => `${x(p.i)},${y(p.v)}`).join(" ")}
                  fill="none"
                  stroke={SERIES_COLORS[si]}
                  strokeWidth="2"
                  strokeLinejoin="round"
                  strokeLinecap="round"
                />
              )}
              {pts.map((p) => (
                <circle key={p.i} cx={x(p.i)} cy={y(p.v)} r={hover === p.i ? 5 : 4} fill={SERIES_COLORS[si]} stroke="#fff" strokeWidth="2" />
              ))}
            </g>
          );
        })}
      </svg>
      {hover !== null && (
        <div
          className="pointer-events-none absolute top-6 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-md"
          style={{ left: `${(x(hover) / W) * 100}%`, transform: "translateX(-50%)" }}
        >
          <p className="mb-1 text-slate-600">{points[hover].label}</p>
          {series.map((s, i) => (
            <p key={s.key} className="flex items-center gap-2">
              <svg width="12" height="4" aria-hidden>
                <line x1="0" y1="2" x2="12" y2="2" stroke={SERIES_COLORS[i]} strokeWidth="2" />
              </svg>
              <strong className="text-slate-900">{points[hover].values[s.key] == null ? "—" : `${points[hover].values[s.key]}%`}</strong>
              <span className="text-slate-600">{s.label}</span>
            </p>
          ))}
        </div>
      )}
    </div>
  );
}

// 横棒（回答に何回出たか）。自店だけ色をつけ、他は灰色にして見比べやすくする。
export function BarList({ rows, max }: { rows: { label: string; value: number; highlight?: boolean; note?: string }[]; max: number }) {
  const [hover, setHover] = useState<number | null>(null);
  return (
    <ul className="space-y-2">
      {rows.map((r, i) => (
        <li
          key={r.label + i}
          tabIndex={0}
          onPointerEnter={() => setHover(i)}
          onPointerLeave={() => setHover(null)}
          onFocus={() => setHover(i)}
          onBlur={() => setHover(null)}
          className="grid grid-cols-[minmax(0,10rem)_1fr_auto] items-center gap-3 rounded-md px-1 py-0.5 text-sm outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
        >
          <span className={`truncate ${r.highlight ? "font-bold text-slate-900" : "text-slate-700"}`} title={r.label}>
            {r.label}
          </span>
          <span className="h-3 rounded-r bg-slate-100">
            <span
              className="block h-3 rounded-r transition-opacity"
              style={{
                width: `${max ? (r.value / max) * 100 : 0}%`,
                background: r.highlight ? SERIES_COLORS[0] : "#94a3b8",
                opacity: hover === null || hover === i ? 1 : 0.55,
              }}
            />
          </span>
          <span className="w-16 text-right text-sm text-slate-700">
            {r.value}回{hover === i && r.note ? <span className="block text-slate-500">{r.note}</span> : null}
          </span>
        </li>
      ))}
    </ul>
  );
}
