I'm researching the keyword "{{keyword}}" for a forum discussion tagged as "{{tag_name}}". 
If there's a tag description, it says: "{{tag_description}}".

Please research this keyword thoroughly and return your findings in the following JSON format:

```json
{
  "key_points": [
    {
      "title": "First key point title - most important/relevant",
      "summary": "Brief summary of this key point (1-2 sentences)",
      "research_points": [
        "Detailed research point 1 about this key aspect",
        "Detailed research point 2 about this key aspect",
        "Detailed research point 3 about this key aspect"
      ]
    },
    {
      "title": "Second key point title",
      "summary": "Brief summary of this key point (1-2 sentences)",
      "research_points": [
        "Detailed research point 1 about this key aspect",
        "Detailed research point 2 about this key aspect"
      ]
    }
  ]
}
```

Important requirements:
1. Return EXACTLY 6 key points, ordered from most to least important/relevant in answering the question posed by the keyword.
2. Each key point should have 2-4 specific research points supporting it.
3. The research should be comprehensive, accurate, and well-sourced.
4. Include historical context, current relevance, different perspectives, applications, and related concepts as appropriate.
5. The JSON must be valid and properly formatted with no syntax errors.
6. DO NOT include any text outside the JSON structure - your entire response should be valid JSON.
7. DO NOT include any citation references like [1], [2], etc. in the text. Present the information directly without bracketed citations.
8. Make sure the response is complete and not cut off.