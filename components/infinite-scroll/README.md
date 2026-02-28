# Infinite scroll Component

This component creates an infinite scroll using htmx, that replaces the pager in Views.

## In the various `views--view--*.html.twig` templates

```
	{% if pager %}	
		{{ include('spruce:infinite-scroll', {
			id: view_id,
			content: rows_content,
			pager: pager,
		}) }}  
	{% endif %}
```

## There is also a dependency on a `#[Hook('preprocess_views_view')]` 
in the themes Hook class, that sets the `next_url` variable.