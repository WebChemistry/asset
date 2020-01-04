### Description

Integration of symfony/asset and symfony/web-link for nette


### Installation

```
composer require webchemistry/asset
```

```yaml
extensions:
    asset: WebChemistry\Asset\DI\AssetExtension
    assetBundle: WebChemistry\Asset\DI\AssetBundleExtension
```

### Assets

```yaml
asset:
	packages:
		default:
			type: WebChemistry\Asset\Packages\BasePathPackage
			arguments:
				- dist
				- Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy(%wwwDir%/dist/manifest.json)
		absolute:
			type: Symfony\Component\Asset\Package
			arguments:
				- Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy()
		cdn:
			type: Symfony\Component\Asset\UrlPackage
			arguments:
				- https://cdnjs.cloudflare.com
				- Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy()
```

### Asset bundles

```yaml
assetBundle:
	front:
		- src.css
		- 'absolute:https://platform.twitter.com/widgets.js'
		- src.js
```

### Usage in latte

```html
{assetBundle front, css}
{assetBundle front, js}
```

without assetBundle
```html
<link rel="stylesheet" n:asset="'src.css'">

<script n:asset="'src.js'"></script>
```
