#!/bin/sh

# 脚本出现错误时退出
set -e

# 确保当前是 Git 仓库
if ! git rev-parse --git-dir > /dev/null 2>&1; then
  echo "❌ 当前目录不是 Git 仓库"
  exit 1
fi

# 获取最后一个 tag（没有的话为空）
last_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "")

if [ -z "$last_tag" ]; then
  echo "⚠️ 未找到 tag，默认初始版本为 0.0.1"
  new_version="v0.0.1"
else
  # 去掉开头的 v（如果有）
  clean_tag=$(echo "$last_tag" | sed 's/^v//')

  # 拆分版本号
  IFS='.' read -r major minor patch <<EOF
$clean_tag
EOF

  # 如果 patch 为空，则设为 0
  patch=${patch:-0}

  # 版本号最后一位 +1
  patch=$((patch + 1))

  # 新版本号，保留 v 前缀
  new_version="v${major}.${minor}.${patch}"
fi

# 输出新版本号
echo "✅ 新版本号：$new_version"

# 创建新 tag
git tag "$new_version"
echo "🏷️  已创建本地 tag：$new_version"

# 推送 tag 到远程
git push origin "$new_version"
echo "🚀 已推送 tag 到远程仓库：origin/$new_version"
