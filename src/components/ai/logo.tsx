// AIススメルのロゴ：Noto Sans JP（太さ900）の「ス」をそのまま形にしたもの（字形は崩さない）
const SU_PATH = "M853 683Q846 673 833.0 651.0Q820 629 812 610Q791 564 760.5 506.5Q730 449 691.5 390.0Q653 331 609 279Q553 214 484.5 151.5Q416 89 340.5 36.5Q265 -16 188 -53L62 78Q143 108 220.0 155.0Q297 202 362.0 256.0Q427 310 471 359Q504 396 531.5 436.0Q559 476 580.0 514.5Q601 553 611 585Q600 585 576.0 585.0Q552 585 521.0 585.0Q490 585 456.5 585.0Q423 585 392.0 585.0Q361 585 336.5 585.0Q312 585 300 585Q278 585 253.5 583.5Q229 582 207.5 580.0Q186 578 172 577V749Q190 747 214.5 745.0Q239 743 262.5 742.0Q286 741 300 741Q315 741 342.0 741.0Q369 741 402.5 741.0Q436 741 471.5 741.0Q507 741 539.5 741.0Q572 741 597.0 741.0Q622 741 634 741Q672 741 704.5 745.5Q737 750 754 756ZM622 379Q660 349 704.0 308.0Q748 267 791.5 222.5Q835 178 872.0 137.0Q909 96 933 67L794 -54Q757 0 710.5 54.5Q664 109 612.5 163.0Q561 217 507 266Z";

export function LogoMark({ className = "h-8 w-8", light }: { className?: string; light?: boolean }) {
  return (
    <svg viewBox="0 0 32 32" className={className} aria-hidden>
      <path d={SU_PATH} transform="translate(1.720 26.075) scale(0.02870 -0.02870)" fill={light ? "#FFFFFF" : "#2563EB"} />
    </svg>
  );
}

// サイトのロゴは文字だけ（「ス」のマークはブラウザのタブ・ホーム画面のアイコンにだけ使う）
export function Logo({ light, size = "md" }: { light?: boolean; size?: "md" | "sm" }) {
  return (
    <span className="inline-flex items-center">
      <span className={`palt whitespace-nowrap font-black leading-none ${size === "sm" ? "text-lg" : "text-[1.4rem]"} ${light ? "text-white" : "text-slate-900"}`}>
        <span className={`font-num font-black text-brand`}>AI</span>ススメル
      </span>
    </span>
  );
}
