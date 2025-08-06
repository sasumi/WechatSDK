#!/bin/sh

set -e

# 确保当前是 Git 仓库
if ! git rev-parse --git-dir > /dev/null 2>&1; then
  echo "❌ 当前目录不是 Git 仓库"
  exit 1
fi

# 获取最后一个 tag（若无则为空）
last_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "")

if [ -z "$last_tag" ]; then
  echo "⚠️ 未找到tag，默认初始版本为 0.0.1"
  new_version="0.0.1"
else
  echo "🔍 当前Tag：$last_tag"
  # 判断是否带 v 前缀
  if echo "$last_tag" | grep -q "^v"; then
    has_v_prefix=true
    clean_tag=$(echo "$last_tag" | sed 's/^v//')
  else
    has_v_prefix=false
    clean_tag="$last_tag"
  fi

  # 拆分版本号为 x.y.z 格式
  IFS='.' read -r major minor patch <<EOF
$clean_tag
EOF

  patch=${patch:-0}
  patch=$((patch + 1))

  new_version="${major}.${minor}.${patch}"
  [ "$has_v_prefix" = true ] && new_version="v$new_version"
fi

echo "✅ 新版本号：$new_version"

# 创建并推送 tag
git tag "$new_version"
echo "🏷️  已创建本地 tag：$new_version"

git push origin "$new_version"
echo "🚀 已推送 tag 到远程仓库：origin/$new_version"
