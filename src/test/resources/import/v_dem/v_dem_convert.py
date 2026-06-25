#!/usr/bin/env python3
"""
Convert a V-Dem Country-Year CSV sample into a zukunft.com JSON.

Scope of this conversion: the affective-polarization indicator v2cacamps
("Political polarization": is society polarized into antagonistic camps?),
which is the variable underlying WTW Political Risk Index H1 2025 Figure 3.

Conventions followed (per the established zukunft.com workflow):
  - Disambiguation is carried in the words[] array, never in free-text notes.
  - Sourced data points are tagged 'measured value'.
  - Each point keeps the V-Dem measurement-model confidence interval
    (codelow / codehigh) so weak estimates can be surfaced and the series
    is Monte-Carlo ready.
  - Two scales are preserved without loss:
      * measurement-model estimate (interval scale, ~ -4..+4, the default
        V-Dem output)  -> field 'mm'
      * original ordinal scale 0..4 (the _osp rescale, matching the WTW
        Figure 3 axis) -> field 'osp'
"""

import csv
import json
import sys


def to_float(text):
    result = None
    stripped = text.strip()
    if stripped != "":
        result = float(stripped)
    return result


def build_series(rows, country):
    data_points = []
    country_rows = [r for r in rows if r["country_name"] == country]
    for row in country_rows:
        year = int(row["year"])
        mm = to_float(row["v2cacamps"])
        mm_low = to_float(row["v2cacamps_codelow"])
        mm_high = to_float(row["v2cacamps_codehigh"])
        mm_sd = to_float(row["v2cacamps_sd"])
        osp = to_float(row["v2cacamps_osp"])
        osp_low = to_float(row["v2cacamps_osp_codelow"])
        osp_high = to_float(row["v2cacamps_osp_codehigh"])
        coders = to_float(row["v2cacamps_nr"])
        point = {
            "year": year,
            "mm": mm,
            "mm_codelow": mm_low,
            "mm_codehigh": mm_high,
            "mm_sd": mm_sd,
            "osp": osp,
            "osp_codelow": osp_low,
            "osp_codehigh": osp_high,
            "coders": coders,
            "qualifier": "measured value",
        }
        data_points.append(point)
    result = data_points
    return result


def build_document(rows):
    countries = []
    seen = []
    for row in rows:
        name = row["country_name"]
        if name not in seen:
            seen.append(name)
            countries.append(name)

    words = [
        {"name": "affective polarization",
         "description": "Degree to which a society is polarized into antagonistic political camps; V-Dem question for v2cacamps."},
        {"name": "polarization index value",
         "description": "V-Dem v2cacamps expert-coded score."},
        {"name": "measurement model estimate",
         "description": "V-Dem point estimate on the interval (latent) scale, roughly -4..+4, higher = more polarized."},
        {"name": "original scale 0 to 4",
         "description": "V-Dem _osp rescaling onto the original ordinal 0..4 scale; matches the WTW Figure 3 axis."},
        {"name": "confidence interval lower bound"},
        {"name": "confidence interval upper bound"},
        {"name": "number of coders",
         "description": "Count of country experts contributing to the estimate (v2cacamps_nr)."},
        {"name": "year"},
        {"name": "measured value",
         "description": "Provenance qualifier: value taken from a stated source."},
        {"name": "v2cacamps",
         "description": "V-Dem variable id for the political polarization (antagonistic camps) indicator."},
    ]
    for country in countries:
        words.append({"name": country})

    verbs = [
        {"name": "is measured by"},
        {"name": "has value for year"},
        {"name": "is a"},
    ]

    triples = [
        {"from": "affective polarization",
         "verb": "is measured by",
         "to": "polarization index value",
         "name": "affective polarization measure (V-Dem v2cacamps)"},
    ]

    sources = [
        {"name": "V-Dem Country-Year Full+Others",
         "type": "dataset",
         "publisher": "Varieties of Democracy (V-Dem) Institute, University of Gothenburg",
         "variable": "v2cacamps (Political polarization)",
         "codebook": "https://www.v-dem.net/documents/70/codebook_v16.pdf",
         "dataset": "https://www.v-dem.net/data/the-v-dem-dataset/",
         "scale": "original ordinal 0..4 (higher = more polarized); _osp rescales the measurement-model estimate back onto this scale",
         "verified": "extracted directly from the provided CSV sample, not cited from memory"},
    ]

    time_series = []
    for country in countries:
        series = build_series(rows, country)
        entry = {
            "name": "Affective political polarization, " + country + " (V-Dem v2cacamps)",
            "phrases": ["affective polarization", country, "polarization index value", "v2cacamps"],
            "source": "V-Dem Country-Year Full+Others",
            "qualifier": "measured value",
            "scale_note_for_user": "Each point carries both the measurement-model estimate (mm, interval scale) with its confidence interval (mm_codelow/mm_codehigh) and the original 0..4 scale value (osp) with its interval. The osp values are the ones comparable to the WTW Figure 3 chart.",
            "data": series,
        }
        time_series.append(entry)

    document = {
        "version": "0.0.4",
        "pod": "zukunft.com",
        "description": "Affective political polarization time series from the V-Dem dataset (variable v2cacamps), the indicator underlying WTW Political Risk Index H1 2025 Figure 3. Built from the provided CSV sample. All data points are sourced and tagged 'measured value', each retaining the V-Dem confidence interval.",
        "words": words,
        "verbs": verbs,
        "triples": triples,
        "sources": sources,
        "time_series": time_series,
    }
    result = document
    return result


def main():
    in_path = sys.argv[1]
    out_path = sys.argv[2]
    with open(in_path, newline="") as f:
        reader = csv.DictReader(f)
        rows = list(reader)
    document = build_document(rows)
    with open(out_path, "w") as f:
        json.dump(document, f, indent=2, ensure_ascii=False)
    print("wrote", out_path)


main()