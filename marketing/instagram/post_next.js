// queue.json の「まだ投稿していない最初のカルーセル」を zaito_work に投稿する。
// 使い方:
//   node post_next.js --dry-run   … 投稿の準備（画像の確認・カルーセルの作成）までして、公開はしない
//   node post_next.js             … 公開する。投稿したら queue.json に日時・ID・URLを書く
// トークンはコードにも出力にも出さない。環境の「ネットワークシークレット」が graph.instagram.com への通信に
// Authorization ヘッダーを付けるので、ここでは curl で普通に呼ぶだけ（Node の fetch はそのプロキシを通らないため curl を使う）。
const cp = require('child_process');
const fs = require('fs');
const path = require('path');

const API = 'https://graph.instagram.com/v21.0';
const QPATH = path.join(__dirname, 'queue.json');
const DRY = process.argv.includes('--dry-run');

function curl(args) {
  const out = cp.execFileSync('curl', ['-sS', '--max-time', '60', ...args], { encoding: 'utf8' });
  let j;
  try { j = JSON.parse(out); } catch (e) { throw new Error('APIの返事を読めませんでした: ' + out.slice(0, 200)); }
  if (j.error) throw new Error('APIエラー: ' + (j.error.message || JSON.stringify(j.error)));
  return j;
}
const get = (p, q = {}) => curl([API + p + '?' + new URLSearchParams(q)]);
const post = (p, form) => curl(['-X', 'POST', API + p, ...Object.entries(form).flatMap(([k, v]) => ['--data-urlencode', `${k}=${v}`])]);
const sleep = ms => Atomics.wait(new Int32Array(new SharedArrayBuffer(4)), 0, 0, ms);

const queue = JSON.parse(fs.readFileSync(QPATH, 'utf8'));
const item = queue.find(q => !q.posted);
if (!item) { console.log('投稿するものがありません（全部投稿済み）'); process.exit(0); }
const tagCount = (item.caption.match(/#[^\s#]+/g) || []).length;
if (tagCount > 5) throw new Error('ハッシュタグは5個までです（' + item.id + ' は' + tagCount + '個）');
console.log((DRY ? '[確認だけ] ' : '') + '投稿するもの: ' + item.id + '（' + item.images.length + '枚）');

// 1. 画像がサイトから見えるか（Instagramが取りに来られるか）
for (const u of item.images) {
  const code = cp.execFileSync('curl', ['-sS', '-o', '/dev/null', '-w', '%{http_code} %{content_type}', u], { encoding: 'utf8' });
  if (!/^200 image\/jpeg/.test(code)) throw new Error('画像が開けません: ' + u + ' → ' + code);
}
const me = get('/me', { fields: 'user_id,username' });
console.log('アカウント: ' + me.username);

// 2. 1枚ずつ「カルーセルの中身」を作り、まとめて1つのカルーセルにする
const children = item.images.map(u => post('/' + me.user_id + '/media', { image_url: u, is_carousel_item: 'true' }).id);
const box = post('/' + me.user_id + '/media', { media_type: 'CAROUSEL', children: children.join(','), caption: item.caption }).id;

// 3. Instagram側の準備ができるまで待つ
let status = '';
for (let i = 0; i < 30; i++) {
  status = get('/' + box, { fields: 'status_code' }).status_code;
  if (status === 'FINISHED') break;
  if (status === 'ERROR' || status === 'EXPIRED') throw new Error('カルーセルの準備に失敗しました: ' + status);
  sleep(5000);
}
if (status !== 'FINISHED') throw new Error('カルーセルの準備が終わりませんでした: ' + status);
console.log('準備できました（' + children.length + '枚）');
if (DRY) { console.log('[確認だけ] 公開はしていません。準備したカルーセルは24時間で自動的に消えます。'); process.exit(0); }

// 4. 公開して、記録する
const media = post('/' + me.user_id + '/media_publish', { creation_id: box }).id;
const link = get('/' + media, { fields: 'permalink' }).permalink;
item.posted = new Date().toISOString();
item.media_id = media;
item.permalink = link;
fs.writeFileSync(QPATH, JSON.stringify(queue, null, 1));
console.log('投稿しました: ' + link);
