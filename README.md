# BB Portfolio Gallery

Egyszerű WordPress galéria plugin képekkel és YouTube shortokkal, masonry layout és lightbox megjelenítéssel.

## Funkciók

- ✅ **Masonry Layout** - Pinterest-szerű reszponzív elrendezés
- ✅ **Képek + YouTube Shortok** - Keverd a médiatípusokat
- ✅ **Lightbox** - Beépített GLightbox
- ✅ **Autoplay Videók** - Lightbox-ban automatikusan indulnak
- ✅ **Reszponzív** - 4/3/2 oszlopos breakpointok
- ✅ **ACF Integráció** - Egyszerű admin felület

## Telepítés

1. Töltsd le vagy klónozd a repo-t
2. Másold a `BB-Portfolio-Gallery` mappát a `/wp-content/plugins/` könyvtárba
3. Telepítsd az **Advanced Custom Fields** plugint
4. Aktiváld mindkét plugint a WordPress admin felületen

## Használat

### Galéria elemek hozzáadása

1. WordPress Admin → **Galéria** → **Új elem**
2. **Kép elem**: Állítsd be a kiemelt képet
3. **Videó elem**: Írd be a YouTube videó ID-t (pl: `dQw4w9WgXcQ`)

### Megjelenítés

Shortcode beszúrása bárhova:
```
[bbloom_gallery]
```

**Paraméterek:**
```
[bbloom_gallery limit="12" order="date" class="my-gallery"]
```

- `limit` - Elemek száma (alapértelmezett: összes)
- `order` - Sorrend: `rand`, `date`, `title` (alapértelmezett: `rand`)
- `class` - Extra CSS osztály

## Követelmények

- WordPress 5.8+
- PHP 7.4+
- Advanced Custom Fields (ingyenes verzió)

## Szerzői Jog

© 2026 Business Bloom Consulting® 

## Support

Kérdés vagy probléma esetén nyiss egy Issue-t a GitHub-on.
