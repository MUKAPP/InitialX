# AGENTS.md

## 项目概述
**InitialX** 是一个基于 [Initial](https://github.com/jielive/initial) 主题二次开发的 Typecho 主题。
该项目包含 PHP 模板文件、SCSS 样式源文件和 JavaScript 逻辑代码。

## 技术栈
- **后端**: PHP (Typecho 模板语法)
- **样式**: SCSS (编译为 CSS), PostCSS (自动添加前缀, 压缩)
- **脚本**: JavaScript (Terser 压缩)
- **构建工具**: npm scripts, sass, postcss, terser, archiver

## 项目结构
主要目录和文件说明：
- `src/` : 源代码目录 (建议修改此处)
    - `src/scss/` : SCSS 样式文件
    - `src/js/` : JavaScript 源码
- `dist/` : 编译后的资源目录 (不要直接修改)
    - `dist/style.css` : 编译后的 CSS
    - `dist/main.min.js` : 压缩后的 JS
- `scripts/` : 构建和打包脚本
- `*.php` : Typecho 主题模板文件 (如 `index.php`, `header.php`, `functions.php`)
- `package.json` : 项目依赖和脚本定义

## 开发指南

### 安装依赖
```bash
npm install
```

### 开发模式
监听 `src/` 目录下的文件变化并自动编译：
```bash
npm run watch
```

### 构建生产环境代码
编译 SCSS 和 JS 并压缩：
```bash
npm run build
```

### 打包主题
更新版本号、构建并将主题打包为 zip 文件：
```bash
npm run pack
```

## 注意事项
1. **优先修改源码**：所有的样式修改应在 `src/scss/` 中进行，JS 修改在 `src/js/` 中进行。切勿直接修改 `dist/` 目录下的文件，因为它们会被构建进程覆盖。
2. **PHP 模板**：PHP 文件位于根目录，可以直接编辑。
3. **版本控制**：主要分支用于开发。
4. **代码风格**：保持现有的缩进和命名规范。
