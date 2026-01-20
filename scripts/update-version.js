const fs = require('fs');
const path = require('path');
const pkg = require('../package.json');

const version = pkg.version;
const rootDir = path.join(__dirname, '..');

console.log(`🔄 Syncing version to ${version}...`);

// Sync index.php
const indexPath = path.join(rootDir, 'index.php');
if (fs.existsSync(indexPath)) {
    let indexContent = fs.readFileSync(indexPath, 'utf8');
    const indexRegex = /(@version\s+)([\d\.]+)/;
    if (indexRegex.test(indexContent)) {
        indexContent = indexContent.replace(indexRegex, `$1${version}`);
        fs.writeFileSync(indexPath, indexContent);
        console.log(`✅ Updated index.php`);
    } else {
        console.warn(`⚠️  Could not find version tag in index.php`);
    }
} else {
    console.error(`❌ index.php not found`);
}

// Sync functions.php
const functionsPath = path.join(rootDir, 'functions.php');
if (fs.existsSync(functionsPath)) {
    let functionsContent = fs.readFileSync(functionsPath, 'utf8');
    const functionsRegex = /(define\('INITIALX_VERSION_NUMBER',\s*')([\d\.]+)('\);)/;
    if (functionsRegex.test(functionsContent)) {
        functionsContent = functionsContent.replace(functionsRegex, `$1${version}$3`);
        fs.writeFileSync(functionsPath, functionsContent);
        console.log(`✅ Updated functions.php`);
    } else {
        console.warn(`⚠️  Could not find INITIALX_VERSION_NUMBER define in functions.php`);
    }
} else {
    console.error(`❌ functions.php not found`);
}
