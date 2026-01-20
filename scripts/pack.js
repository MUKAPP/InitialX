const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const pkg = require('../package.json');
const themeName = 'InitialX';
const version = pkg.version;
const outputDir = path.join(__dirname, '..');
const outputFile = path.join(outputDir, `${themeName}-v${version}.zip`);

// 需要排除的文件和目录
const excludePatterns = [
  'node_modules',
  '.git',
  '.gitignore',
  'src',
  'scripts',
  'package.json',
  'package-lock.json',
  'postcss.config.js',
  '.stylelintrc.json',
  '*.zip',
  'build.bat',
  'banner.png'
];

// 检查是否应该排除
function shouldExclude(filePath) {
  const relativePath = path.relative(outputDir, filePath);
  return excludePatterns.some(pattern => {
    if (pattern.startsWith('*')) {
      return relativePath.endsWith(pattern.slice(1));
    }
    return relativePath === pattern || relativePath.startsWith(pattern + path.sep);
  });
}

async function pack() {
  console.log('📦 正在打包主题...\n');

  // 确保构建目录存在且已构建
  if (!fs.existsSync(path.join(outputDir, 'dist', 'style.min.css'))) {
    console.error('❌ 请先运行 npm run build 构建项目');
    process.exit(1);
  }

  // 删除旧的打包文件
  if (fs.existsSync(outputFile)) {
    fs.unlinkSync(outputFile);
  }

  const output = fs.createWriteStream(outputFile);
  const archive = archiver('zip', { zlib: { level: 9 } });

  output.on('close', () => {
    const size = (archive.pointer() / 1024).toFixed(2);
    console.log(`\n✅ 打包完成！`);
    console.log(`   文件: ${path.basename(outputFile)}`);
    console.log(`   大小: ${size} KB`);
  });

  archive.on('error', (err) => {
    throw err;
  });

  archive.pipe(output);

  // 遍历目录添加文件
  function addDirectory(dir, archivePath = '') {
    const items = fs.readdirSync(dir);
    for (const item of items) {
      const fullPath = path.join(dir, item);
      const archiveItemPath = path.join(archivePath, item);
      
      if (shouldExclude(fullPath)) {
        continue;
      }

      const stat = fs.statSync(fullPath);
      if (stat.isDirectory()) {
        addDirectory(fullPath, archiveItemPath);
      } else {
        archive.file(fullPath, { name: path.join(themeName, archiveItemPath) });
        console.log(`   添加: ${archiveItemPath}`);
      }
    }
  }

  addDirectory(outputDir);
  await archive.finalize();
}

pack().catch(console.error);
