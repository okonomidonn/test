# ナレーションの無音を詰め、必要なら速めて target 秒に収める
import subprocess, re, sys
F='/usr/local/lib/python3.11/dist-packages/imageio_ffmpeg/binaries/ffmpeg-linux-x86_64-v7.0.2'
src, out, target = sys.argv[1], sys.argv[2], 7.4
log = subprocess.run([F,'-i',src,'-af','silencedetect=n=-40dB:d=0.15','-f','null','-'],capture_output=True,text=True).stderr
dur = float(re.search(r'Duration: (\d+):(\d+):([\d.]+)',log).group(3))
sil = [(float(a), float(b)) for a,b in zip(re.findall(r'silence_start: ([\d.]+)',log), re.findall(r'silence_end: ([\d.]+)',log)+[str(dur)])]
# 声の区間 = 無音のすき間
segs=[]; t=0
for a,b in sil:
    if a>t: segs.append((max(0,t-0.04), a+0.04))
    t=b
if t<dur-0.05: segs.append((t-0.04,dur))
gaps=[]
for i in range(len(segs)-1):
    g = sil[[j for j,(a,b) in enumerate(sil) if abs(a-(segs[i][1]-0.04))<1e-3][0]]
    gaps.append(0.15 if g[1]-g[0]>0.7 else 0.06)
gaps.append(0)
speech=sum(b-a for a,b in segs)+sum(gaps)
tempo=max(1.0, round(speech/target+0.005,2))
parts=[];labels=[]
for i,(a,b) in enumerate(segs):
    parts.append(f"[0:a]atrim={a:.3f}:{b:.3f},asetpts=PTS-STARTPTS[s{i}]"); labels.append(f"[s{i}]")
    if gaps[i]>0: parts.append(f"anullsrc=r=22050:cl=mono,atrim=0:{gaps[i]}[g{i}]"); labels.append(f"[g{i}]")
parts.append("".join(labels)+f"concat=n={len(labels)}:v=0:a=1,atempo={tempo},adelay=150,apad=whole_dur=8,afade=t=out:st=7.8:d=0.2[out]")
subprocess.run([F,'-y','-loglevel','error','-i',src,'-filter_complex',';'.join(parts),'-map','[out]','-t','8','-ar','44100','-ac','2',out],check=True)
print(f"{src.split('/')[-1]}: 元 {dur:.2f}秒 / 区間 {len(segs)} / 詰めた後 {speech:.2f}秒 / 速さ {tempo}倍")
