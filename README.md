### Description

Integration of symfony/asset for nette


### Installation

```
composer require webchemistry/asset
```

```yaml
extensions:
    asset: WebChemistry\Asset\DI\AssetExtension
```

### Assets

```yaml
asset:
	packages:
		# first is default
		default: @WebChemistry\Asset\Package\BasePathPackageFactory::create()
```

### Usage in latte

```html
{asset app.css}
{asset app.css, 'default'}
```

```html
<img n:asset="app.jpg">

<link rel="stylesheet" n:asset="app.css">

<script n:asset="app.js"></script>
```
