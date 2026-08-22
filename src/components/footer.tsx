import Link from "next/link";

export function Footer() {
  return (
    <footer
      style={{
        padding: "42px 0",
        color: "#7a808c",
        fontSize: "12px",
        display: "flex",
        justifyContent: "space-between",
        borderTop: "1px solid var(--line)",
        marginTop: "80px",
        width: "min(1160px, calc(100% - 40px))",
        margin: "80px auto 0",
      }}
    >
      <strong style={{ color: "var(--ink)" }}>zaito</strong>
      <span>在宅ワーク求人サイト　© zaito {new Date().getFullYear()}</span>
    </footer>
  );
}
