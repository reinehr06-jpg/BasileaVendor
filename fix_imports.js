const fs = require('fs');
const path = require('path');

function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    file = dir + '/' + file;
    const stat = fs.statSync(file);
    if (stat && stat.isDirectory()) {
      results = results.concat(walk(file));
    } else {
      results.push(file);
    }
  });
  return results;
}

const files = walk('./frontend/src/app').filter(f => f.endsWith('.tsx') || f.endsWith('.ts'));

files.forEach(f => {
  let content = fs.readFileSync(f, 'utf8');
  if (content.includes('useEffect') && !content.includes('useEffect } from') && !content.includes(', useEffect}') && !content.includes('useEffect, ') && !content.includes('{useEffect') && !content.includes('{ useEffect')) {
    content = content.replace(/import React, \{ useState \} from ["']react["'];/, 'import React, { useState, useEffect } from "react";');
    content = content.replace(/import \{ useState \} from ["']react["'];/, 'import { useState, useEffect } from "react";');
    fs.writeFileSync(f, content);
    console.log("Fixed: " + f);
  }
});
