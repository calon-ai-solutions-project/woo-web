#!/usr/bin/env python3
"""Build the theme logo files from brand/tokens.json.

The brand SVGs in brand/ use live <text>, which only renders correctly where
Anton and Barlow Condensed are installed. A logo loaded with <img>, or used as
a favicon, cannot see the page's web fonts, so this script redraws the same
layout with the letters converted to outlines.

Output (wp-content/themes/clo-child/assets/img/):
  logo.svg           navy wordmark, for light backgrounds
  logo-reversed.svg  white wordmark, for the navy header and footer
  logo-icon.svg      round CLO sticker, used as the site icon

Run from the repo root:
  pip install fonttools brotli
  python3 scripts/build-logos.py

Kerning pairs are not applied; glyphs are placed on their advance widths plus
the letter spacing from the brand SVGs.
"""
import json
from pathlib import Path

from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont

ROOT = Path(__file__).resolve().parent.parent
THEME = ROOT / "wp-content/themes/clo-child"
FONTS = THEME / "assets/fonts"
OUT = THEME / "assets/img"

tokens = json.loads((ROOT / "brand/tokens.json").read_text())
NAVY = tokens["colors"]["navy"]
YELLOW = tokens["colors"]["stickerYellow"]
WHITE = tokens["colors"]["white"]
TILT = tokens["logo"]["stickerRotationDeg"]
LINES = tokens["logo"]["lines"]

anton = TTFont(FONTS / "anton-latin-400-normal.woff2")
barlow = TTFont(FONTS / "barlow-condensed-latin-600-normal.woff2")


def text_path(font, text, x, baseline, size, spacing=0.0, anchor="start"):
    """Return SVG path data for `text`, laid out like an SVG <text> element."""
    glyphs = font.getGlyphSet()
    cmap = font.getBestCmap()
    scale = size / font["head"].unitsPerEm
    names = [cmap[ord(ch)] for ch in text]
    advances = [glyphs[n].width * scale + spacing for n in names]
    cursor = x - sum(advances) / 2 if anchor == "middle" else x
    parts = []
    for name, advance in zip(names, advances):
        pen = SVGPathPen(glyphs, ntos=lambda v: f"{v:.2f}".rstrip("0").rstrip("."))
        glyphs[name].draw(TransformPen(pen, (scale, 0, 0, -scale, cursor, baseline)))
        parts.append(pen.getCommands())
        cursor += advance
    return "".join(parts)


def wordmark(text_fill):
    top = text_path(barlow, LINES[0], 24, 60, 40, 5.6)
    middle = text_path(barlow, LINES[1], 24, 104, 40, 5.6)
    outlet = text_path(anton, LINES[2], 224, 250, 128, 2.5, "middle")
    # viewBox is cropped to the artwork so the logo fills the height it is given.
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="16 22 426 270" role="img" aria-label="{tokens['name']}">
  <path fill="{text_fill}" d="{top}{middle}"/>
  <g transform="rotate({TILT} 224 201)">
    <rect x="24" y="126" width="400" height="150" rx="12" fill="{YELLOW}"/>
    <path fill="{NAVY}" d="{outlet}"/>
  </g>
</svg>
"""


def icon():
    clo = text_path(anton, "CLO", 256, 322, 190, 0, "middle")
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="CL Outlet">
  <g transform="rotate({TILT} 256 256)">
    <circle cx="256" cy="256" r="240" fill="{YELLOW}"/>
    <path fill="{NAVY}" d="{clo}"/>
  </g>
</svg>
"""


OUT.mkdir(parents=True, exist_ok=True)
for name, svg in {
    "logo.svg": wordmark(NAVY),
    "logo-reversed.svg": wordmark(WHITE),
    "logo-icon.svg": icon(),
}.items():
    (OUT / name).write_text(svg)
    print(f"wrote {(OUT / name).relative_to(ROOT)} ({len(svg)} bytes)")
