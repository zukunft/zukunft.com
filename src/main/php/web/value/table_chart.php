<?php

/*

    web/value/table_chart.php - a value table drawn as an svg chart
    -------------------------

    renders the rows and columns of a table_model (see value_list::table_to_svg) as one of the
    chart types of shared/enum/chart_types.php: the range bars show one number per row with
    its probability range, the scatter plot places each row by two of its numbers; every row
    carries the numbers of all value columns of the table as its tooltip, so the chart tells
    the reader the same as the table


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\value;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'sandbox_value.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'table_model.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'chart_types.php';
include_once html_paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\chart_types;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

class table_chart
{

    // the base size of the charts in pixel: every length below is a multiple of it, so that a
    // chart can be scaled by this one number
    const float SIZE = 4;
    // the font sizes follow the text size, so that a bigger text makes the title bigger too
    const float TEXT_SIZE = self::SIZE * 3;
    const float TITLE_SIZE = self::TEXT_SIZE * 1.5;
    const float LABEL_SIZE = self::TEXT_SIZE * 1.1;
    const float POINT_TEXT_SIZE = self::TEXT_SIZE * 0.9;
    // the layout shared by the charts: the title lines on top, the axis labels below the plot
    // and the axis title below the labels
    const float TEXT_X = self::SIZE * 5;
    const float TITLE_Y = self::SIZE * 6;
    const float SUBTITLE_Y = self::SIZE * 10.5;
    const float AXIS_GAP = self::SIZE * 4.5;
    const float AXIS_TITLE_GAP = self::SIZE * 10;
    const float BOTTOM_GAP = self::SIZE * 3.5;
    // a text is placed by its baseline, so it is moved down by this to centre it on its y
    const float BASELINE_SHIFT = self::SIZE;
    const float GRID_WIDTH = self::SIZE / 4;
    // the layout of the range bars: one row per table row below the title lines, the row name
    // left of the plot and the number right of the range bar
    const float BAR_WIDTH = self::SIZE * 185;
    const float BAR_ROW_START = self::SIZE * 13;
    const float BAR_ROW_HEIGHT = self::SIZE * 7;
    const float BAR_LABEL_X = self::SIZE * 42.5;
    const float BAR_PLOT_START = self::SIZE * 45;
    const float BAR_PLOT_END = self::SIZE * 175;
    const float BAR_HEIGHT = self::SIZE * 2.5;
    const float DOT_RADIUS = self::SIZE * 1.75;
    const float VALUE_GAP = self::SIZE * 2;
    // the grid lines start this much above the first row
    const float GRID_OVERHANG = self::SIZE / 2;
    // the layout of the scatter plot: the plot below the title lines and the numbered legend
    // in two columns below the axis
    const float SCATTER_WIDTH = self::SIZE * 160;
    const float SCATTER_PLOT_LEFT = self::SIZE * 15;
    const float SCATTER_PLOT_RIGHT = self::SIZE * 155;
    const float SCATTER_PLOT_TOP = self::SIZE * 14;
    const float SCATTER_PLOT_BOTTOM = self::SIZE * 91.5;
    const float SCATTER_TICK_GAP = self::SIZE * 5;
    const float AXIS_TITLE_X = self::SIZE * 4;
    const float POINT_RADIUS = self::SIZE * 2.75;
    const float LEGEND_START = self::SIZE * 109;
    const float LEGEND_LINE = self::SIZE * 5;
    const array LEGEND_COLUMN_X = [self::SIZE * 10, self::SIZE * 85];
    // the leverage plot uses the plot area of the scatter plot without the legend below it
    const float LEVERAGE_HEIGHT = self::SCATTER_PLOT_BOTTOM + self::AXIS_TITLE_GAP + self::BOTTOM_GAP;
    const float LEVERAGE_POINT_RADIUS = self::SIZE * 2;
    const float LEVERAGE_LABEL_GAP = self::SIZE * 3;
    const float QUADRANT_TEXT_GAP = self::SIZE * 2;
    // at most this number of lines of an equal ratio, so that the plot stays readable
    const int RATIO_LINES = 3;
    // the factor that turns a 1, 2 or 5 times a power of ten into the next one (see ratios)
    const float NEXT_NICE_FACTOR = 1.5;
    const string RATIO_PREFIX = '×';
    // the axis of a linear scale has about this many ticks and leaves room above the biggest number
    const int TICKS = 4;
    const float HEADROOM = 1.1;
    // a log scale is used if the biggest number is at least this many times the smallest
    const int LOG_RATIO = 100;
    // the keys of the scale array of scale()
    const string SCALE_LOG = 'log';
    const string SCALE_LOW = 'low';
    const string SCALE_HIGH = 'high';
    const string SCALE_TICKS = 'ticks';
    const string SCALE_START = 'start';
    const string SCALE_END = 'end';
    // the separators of the texts
    const string TITLE_SEP = ': ';
    const string LABEL_SEP = ': ';
    const string LEGEND_SEP = ' · ';
    const string NEWLINE = "\n";

    // the colours of the charts as css variables, once for the light and once for the dark
    // mode: the background, the text, the muted text, the grid, the range bar, the highlight of
    // the number, the points and the hovered row
    const array COLORS_LIGHT = [
        'bg' => '#fbfaf7', 'fg' => '#1d1d1b', 'muted' => '#77756f', 'grid' => '#e3e0d8',
        'range' => '#f0c9a8', 'accent' => '#c4561c', 'accent2' => '#2d6a8a', 'hover' => '#f4efe6',
    ];
    const array COLORS_DARK = [
        'bg' => '#1b1b1a', 'fg' => '#ecebe6', 'muted' => '#9a978f', 'grid' => '#353431',
        'range' => '#6b3d22', 'accent' => '#f08a4b', 'accent2' => '#4f93b8', 'hover' => '#262523',
    ];
    // the number inside a point is white on the point colour in both modes
    const string COLOR_POINT_TEXT = '#fff';

    // the css of the charts; the colours are added by style()
    const string STYLE_SHARED = '
        svg { font-family: system-ui, sans-serif; }
        .bg { fill: var(--bg); }
        .h { fill: var(--fg); font-size: ' . self::TITLE_SIZE . 'px; font-weight: 600; }
        .sub { fill: var(--muted); font-size: ' . self::TEXT_SIZE . 'px; }
        .grid { stroke: var(--grid); stroke-width: ' . self::GRID_WIDTH . '; }
        .ax { fill: var(--muted); font-size: ' . self::TEXT_SIZE . 'px; }';
    const string STYLE_RANGE_BARS = '
        .lbl { fill: var(--fg); font-size: ' . self::LABEL_SIZE . 'px; text-anchor: end; dominant-baseline: central; }
        .val { fill: var(--muted); font-size: ' . self::TEXT_SIZE . 'px; dominant-baseline: central; }
        .rng { fill: var(--range); }
        .mid { fill: var(--accent); }
        .hit { fill: transparent; }
        .bar:hover .hit { fill: var(--hover); }';
    const string STYLE_SCATTER = '
        .pt circle { fill: var(--accent2); }
        .pt text { fill: ' . self::COLOR_POINT_TEXT . '; font-size: ' . self::POINT_TEXT_SIZE . 'px; font-weight: 600; text-anchor: middle; }
        .pt:hover circle { fill: var(--accent); }
        .lg { fill: var(--fg); font-size: ' . self::TEXT_SIZE . 'px; }
        .lg tspan.p { fill: var(--muted); }
        .lg tspan.n { font-weight: 600; fill: var(--accent2); }';
    const string STYLE_LEVERAGE = '
        .pt circle { fill: var(--accent2); }
        .pt:hover circle { fill: var(--accent); }
        .pt .lbl { fill: var(--fg); font-size: ' . self::LABEL_SIZE . 'px; dominant-baseline: central; }
        .q-high { fill: var(--accent2); opacity: 0.07; }
        .q-low { fill: var(--accent); opacity: 0.07; }
        .ratio { stroke: var(--muted); stroke-dasharray: 4 4; opacity: 0.6; }
        .ratio-txt { fill: var(--muted); font-size: ' . self::POINT_TEXT_SIZE . 'px; }';


    /*
     * chart
     */

    /**
     * the table as a chart of the given type
     *
     * @param table_model $model the rows and columns of the table
     * @param chart_types $type how the rows are drawn
     * @param user_message $msg to report a plotted column that the table does not have
     * @param phrase_list $context_phr_lst the phrases of the page e.g. "global problem", which name the chart
     * @param array $chart_cols the names of the value columns to plot, the y axis first: one for
     *                          the range bars, two for the scatter plot; empty for the first
     *                          value columns of the table
     * @return string the svg code of the chart or '' if the table has not the columns to plot
     */
    function svg(
        table_model  $model,
        chart_types  $type,
        user_message $msg,
        phrase_list  $context_phr_lst,
        array        $chart_cols = []
    ): string
    {
        $result = '';
        $col_ids = $this->chart_col_ids($model, $type, $chart_cols, $msg);
        if (count($col_ids) < $type->column_count()) {
            $msg->add_warning_with_vars(msg_id::CHART_COLUMNS_MISSING, []);
        } else {
            $result = match ($type) {
                chart_types::RANGE_BARS => $this->range_bars($model, $col_ids[0], $msg, $context_phr_lst),
                chart_types::SCATTER => $this->scatter($model, $col_ids[0], $col_ids[1], $msg, $context_phr_lst),
                chart_types::LEVERAGE => $this->leverage($model, $col_ids[0], $col_ids[1], $msg, $context_phr_lst),
            };
        }
        return $result;
    }

    /**
     * the ids of the value columns to plot
     *
     * @param table_model $model the rows and columns of the table
     * @param chart_types $type the chart type, which says how many columns are plotted
     * @param array $chart_cols the names of the columns to plot or empty for the first columns
     * @param user_message $msg to report a name that selects no value column of the table
     * @return array the ids of the columns found, in the order of the names
     */
    private function chart_col_ids(table_model $model, chart_types $type, array $chart_cols, user_message $msg): array
    {
        $result = [];
        if ($chart_cols == []) {
            // without a selection the first value columns are plotted, one per phrase, so a
            // further unit of the same phrase is left to the tooltip
            $first_units = array_filter($model->value_col_ids(),
                fn($col_id) => !str_contains((string)$col_id, value_list::UNIT_COLUMN_SEP));
            $result = array_slice(array_values($first_units), 0, $type->column_count());
        } else {
            foreach ($chart_cols as $name) {
                $col_id = $model->col_id_by_name($name);
                if ($col_id === null) {
                    $msg->add_warning_with_vars(msg_id::CHART_COLUMN_NOT_FOUND, [msg_id::VAR_NAME => $name]);
                } else {
                    $result[] = $col_id;
                }
            }
        }
        return $result;
    }


    /*
     * range bars
     */

    /**
     * one row per table row with a dot for the number of the plotted column and a bar for its
     * range, in the order of the table or else the biggest number on top (see bar_rows)
     *
     * @param table_model $model the rows and columns of the table
     * @param int|string $col_id the id of the plotted value column
     * @param user_message $msg to report a problem while formatting a number
     * @param phrase_list $context_phr_lst the phrases of the page, which name the chart
     * @return string the svg code of the chart
     */
    private function range_bars(
        table_model  $model,
        int|string   $col_id,
        user_message $msg,
        phrase_list  $context_phr_lst
    ): string
    {
        $html = new html_base();
        $rows = $this->bar_rows($model, $col_id);
        $scale = $this->scale($this->bar_numbers($rows), self::BAR_PLOT_START, self::BAR_PLOT_END);
        $bottom = self::BAR_ROW_START + count($rows) * self::BAR_ROW_HEIGHT;
        $height = $bottom + self::AXIS_TITLE_GAP + self::BOTTOM_GAP;
        $title = $this->chart_title($context_phr_lst, $this->column_text($model, $col_id));
        $subtitle = msg_id::CHART_RANGE_BARS_TIP->text();
        if ($scale[self::SCALE_LOG]) {
            $subtitle .= self::LEGEND_SEP . msg_id::CHART_LOG_SCALE->text();
        }
        $result = $this->svg_start(self::BAR_WIDTH, $height, self::STYLE_RANGE_BARS, $title);
        $result .= $this->heading($html->esc($title), $html->esc($subtitle));
        // the grid lines of the ticks, from above the first row to below the last one
        $result .= '<g class="grid">' . self::NEWLINE;
        foreach ($scale[self::SCALE_TICKS] as $tick) {
            $x = $this->pos($scale, $tick);
            $result .= $this->line($x, self::BAR_ROW_START - self::GRID_OVERHANG, $x, $bottom);
        }
        $result .= '</g>' . self::NEWLINE;
        $y = self::BAR_ROW_START;
        foreach ($rows as [$row_key, $centre, $low, $high]) {
            $result .= $this->bar_row($model, $row_key, $centre, $low, $high, $scale, $y, $msg);
            $y += self::BAR_ROW_HEIGHT;
        }
        // the tick labels and the axis title centred below the plot
        $result .= '<g class="ax" text-anchor="middle">' . self::NEWLINE;
        foreach ($scale[self::SCALE_TICKS] as $tick) {
            $result .= $this->text($this->pos($scale, $tick), $bottom + self::AXIS_GAP, $this->tick_text($tick));
        }
        $axis_x = (self::BAR_PLOT_START + self::BAR_PLOT_END) / 2;
        $result .= $this->text($axis_x, $bottom + self::AXIS_TITLE_GAP, $html->esc($this->unit_text($model, $col_id)));
        $result .= '</g>' . self::NEWLINE;
        return $result . '</svg>';
    }

    /**
     * the rows of the range bars: every shown row with a number in the plotted column, in the
     * order of the table if the url or the definition has sorted it, else the biggest number first
     *
     * @param table_model $model the rows and columns of the table
     * @param int|string $col_id the id of the plotted value column
     * @return array per row the row key, the centre value, the low bound and the high bound
     */
    private function bar_rows(table_model $model, int|string $col_id): array
    {
        $result = [];
        foreach ($model->shown_keys as $row_key) {
            [$centre, $low, $high,] = $model->cell_numbers($row_key, $col_id);
            if ($centre?->number() !== null) {
                $result[] = [$row_key, $centre, $low, $high];
            }
        }
        if ($model->orders == []) {
            usort($result, fn($a, $b) => $b[1]->number() <=> $a[1]->number());
        }
        return $result;
    }

    /**
     * @param array $rows the rows of bar_rows
     * @return array every number that the plot has to cover: the centre values and the bounds
     */
    private function bar_numbers(array $rows): array
    {
        $result = [];
        foreach ($rows as [, $centre, $low, $high]) {
            foreach ([$centre, $low, $high] as $val) {
                if ($val?->number() !== null) {
                    $result[] = $val->number();
                }
            }
        }
        return $result;
    }

    /**
     * one row of the range bars with the tooltip of the whole table row
     *
     * @param table_model $model the rows and columns of the table
     * @param string $row_key the key of the row
     * @param sandbox_value $centre the number shown as the dot
     * @param sandbox_value|null $low the low bound of the range or null if the number has none
     * @param sandbox_value|null $high the high bound of the range or null if the number has none
     * @param array $scale the scale of the plot (see scale)
     * @param float $y the top of the row in pixel
     * @param user_message $msg to report a problem while formatting a number
     * @return string the svg code of the row
     */
    private function bar_row(
        table_model    $model,
        string         $row_key,
        sandbox_value  $centre,
        ?sandbox_value $low,
        ?sandbox_value $high,
        array          $scale,
        float          $y,
        user_message   $msg
    ): string
    {
        $html = new html_base();
        $mid_y = $y + self::BAR_ROW_HEIGHT / 2;
        $x = $this->pos($scale, $centre->number());
        // a number without a range gets a bar of the width of the dot
        $x_low = ($low?->number() !== null) ? $this->pos($scale, $low->number()) : $x - self::DOT_RADIUS;
        $x_high = ($high?->number() !== null) ? $this->pos($scale, $high->number()) : $x + self::DOT_RADIUS;
        // not "row" like the reference svg, because the page css of bootstrap gives every child
        // of a .row the full width, which would stretch the range bar to the right edge
        $result = '<g class="bar">' . self::NEWLINE;
        $result .= '<title>' . $this->tooltip($model, $row_key, $msg) . '</title>' . self::NEWLINE;
        $result .= '<rect class="hit" x="0" y="' . $y . '" width="' . self::BAR_WIDTH
            . '" height="' . self::BAR_ROW_HEIGHT . '"/>' . self::NEWLINE;
        $result .= '<text class="lbl" x="' . self::BAR_LABEL_X . '" y="' . $mid_y . '">'
            . $html->esc($row_key) . '</text>' . self::NEWLINE;
        $result .= '<rect class="rng" x="' . $x_low . '" y="' . ($mid_y - self::BAR_HEIGHT / 2)
            . '" width="' . round($x_high - $x_low, 1) . '" height="' . self::BAR_HEIGHT
            . '" rx="' . self::BAR_HEIGHT / 2 . '"/>' . self::NEWLINE;
        $result .= '<circle class="mid" cx="' . $x . '" cy="' . $mid_y . '" r="' . self::DOT_RADIUS . '"/>' . self::NEWLINE;
        $result .= '<text class="val" x="' . ($x_high + self::VALUE_GAP) . '" y="' . $mid_y . '">'
            . $centre->value($msg) . '</text>' . self::NEWLINE;
        return $result . '</g>' . self::NEWLINE;
    }


    /*
     * scatter
     */

    /**
     * one numbered point per table row placed by two numbers of the row, with the numbers
     * explained in a legend below the plot
     *
     * @param table_model $model the rows and columns of the table
     * @param int|string $y_col_id the id of the value column shown on the vertical axis
     * @param int|string $x_col_id the id of the value column shown on the horizontal axis
     * @param user_message $msg to report a problem while formatting a number
     * @param phrase_list $context_phr_lst the phrases of the page, which name the chart if the
     *                                     table has no phrase column
     * @return string the svg code of the chart
     */
    private function scatter(
        table_model  $model,
        int|string   $y_col_id,
        int|string   $x_col_id,
        user_message $msg,
        phrase_list  $context_phr_lst
    ): string
    {
        $html = new html_base();
        $points = $this->scatter_points($model, $y_col_id, $x_col_id);
        $x_scale = $this->scale(array_merge([0], array_column($points, 3)), self::SCATTER_PLOT_LEFT, self::SCATTER_PLOT_RIGHT);
        $y_scale = $this->scale(array_merge([0], array_column($points, 2)), self::SCATTER_PLOT_BOTTOM, self::SCATTER_PLOT_TOP);
        $legend_lines = (int)ceil(count($points) / count(self::LEGEND_COLUMN_X));
        $height = self::LEGEND_START + $legend_lines * self::LEGEND_LINE + self::BOTTOM_GAP;
        $y_text = $this->column_text($model, $y_col_id);
        $x_text = $this->column_text($model, $x_col_id);
        $point_col_id = $this->point_column($model, $y_col_id);
        $title = $this->chart_title($this->legend_phrases($model, $context_phr_lst, $point_col_id),
            $y_text . ' ' . msg_id::CHART_VERSUS->text() . ' ' . $x_text);
        $result = $this->svg_start(self::SCATTER_WIDTH, $height, self::STYLE_SCATTER, $title);
        $result .= $this->heading($html->esc($title), $html->esc(msg_id::CHART_SCATTER_TIP->text()));
        $result .= $this->scatter_axes($x_scale, $y_scale, $html->esc($x_text), $html->esc($y_text));
        $nbr = 1;
        foreach ($points as [$row_key, , $y_nbr, $x_nbr]) {
            $result .= $this->point($model, $row_key, $nbr, $this->pos($x_scale, $x_nbr), $this->pos($y_scale, $y_nbr), $msg);
            $nbr++;
        }
        return $result . $this->legend($model, $points, $legend_lines, $point_col_id) . '</svg>';
    }

    /**
     * the points of the scatter plot: every shown row with a number in both plotted columns, in
     * the order of the table
     *
     * @param table_model $model the rows and columns of the table
     * @param int|string $y_col_id the id of the value column of the vertical axis
     * @param int|string $x_col_id the id of the value column of the horizontal axis
     * @return array per point the row key, the row number starting at 1, the y number and the x number
     */
    private function scatter_points(table_model $model, int|string $y_col_id, int|string $x_col_id): array
    {
        $result = [];
        foreach ($model->shown_keys as $row_key) {
            [$y_val, , ,] = $model->cell_numbers($row_key, $y_col_id);
            [$x_val, , ,] = $model->cell_numbers($row_key, $x_col_id);
            if ($y_val?->number() !== null and $x_val?->number() !== null) {
                $result[] = [$row_key, count($result) + 1, $y_val->number(), $x_val->number()];
            }
        }
        return $result;
    }

    /**
     * the grid, the tick labels and the axis titles of the scatter plot
     *
     * @param array $x_scale the scale of the horizontal axis (see scale)
     * @param array $y_scale the scale of the vertical axis
     * @param string $x_title the escaped title of the horizontal axis
     * @param string $y_title the escaped title of the vertical axis
     * @return string the svg code of the axes
     */
    private function scatter_axes(array $x_scale, array $y_scale, string $x_title, string $y_title): string
    {
        $result = '<g class="grid">' . self::NEWLINE;
        foreach ($y_scale[self::SCALE_TICKS] as $tick) {
            $y = $this->pos($y_scale, $tick);
            $result .= $this->line(self::SCATTER_PLOT_LEFT, $y, self::SCATTER_PLOT_RIGHT, $y);
        }
        foreach ($x_scale[self::SCALE_TICKS] as $tick) {
            $x = $this->pos($x_scale, $tick);
            $result .= $this->line($x, self::SCATTER_PLOT_TOP, $x, self::SCATTER_PLOT_BOTTOM);
        }
        $result .= '</g>' . self::NEWLINE . '<g class="ax">' . self::NEWLINE;
        foreach ($y_scale[self::SCALE_TICKS] as $tick) {
            $result .= $this->text(self::SCATTER_PLOT_LEFT - self::VALUE_GAP,
                $this->pos($y_scale, $tick) + self::BASELINE_SHIFT, $this->tick_text($tick), 'text-anchor="end"');
        }
        foreach ($x_scale[self::SCALE_TICKS] as $tick) {
            $result .= $this->text($this->pos($x_scale, $tick), self::SCATTER_PLOT_BOTTOM + self::SCATTER_TICK_GAP,
                $this->tick_text($tick), 'text-anchor="middle"');
        }
        $mid_x = (self::SCATTER_PLOT_LEFT + self::SCATTER_PLOT_RIGHT) / 2;
        $mid_y = (self::SCATTER_PLOT_TOP + self::SCATTER_PLOT_BOTTOM) / 2;
        $result .= $this->text($mid_x, self::SCATTER_PLOT_BOTTOM + self::AXIS_TITLE_GAP, $x_title, 'text-anchor="middle"');
        $result .= $this->text(self::AXIS_TITLE_X, $mid_y, $y_title,
            'text-anchor="middle" transform="rotate(-90 ' . self::AXIS_TITLE_X . ' ' . $mid_y . ')"');
        return $result . '</g>' . self::NEWLINE;
    }

    /**
     * one numbered point of the scatter plot with the tooltip of the whole table row
     *
     * @param table_model $model the rows and columns of the table
     * @param string $row_key the key of the row
     * @param int $nbr the number of the point, which the legend explains
     * @param float $x the horizontal position of the point in pixel
     * @param float $y the vertical position of the point in pixel
     * @param user_message $msg to report a problem while formatting a number
     * @return string the svg code of the point
     */
    private function point(table_model $model, string $row_key, int $nbr, float $x, float $y, user_message $msg): string
    {
        $result = '<g class="pt"><title>' . $nbr . ' ' . $this->tooltip($model, $row_key, $msg) . '</title>';
        $result .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . self::POINT_RADIUS . '"/>';
        $result .= '<text x="' . $x . '" y="' . ($y + self::BASELINE_SHIFT) . '">' . $nbr . '</text></g>' . self::NEWLINE;
        return $result;
    }

    /**
     * the legend of the scatter plot: the number of each point with the phrase of the row, e.g.
     * "1 reduce climate gas emissions · global warming", in the columns of LEGEND_COLUMN_X
     *
     * @param table_model $model the rows and columns of the table
     * @param array $points the points of scatter_points
     * @param int $lines the number of legend lines per column
     * @param int|string|null $point_col_id the phrase column that names the points (see point_column) or null to name them by the row
     * @return string the svg code of the legend
     */
    private function legend(table_model $model, array $points, int $lines, int|string|null $point_col_id): string
    {
        $html = new html_base();
        $result = '<g class="lg">' . self::NEWLINE;
        foreach ($points as $pos => [$row_key, $nbr]) {
            $column = $lines > 0 ? intdiv($pos, $lines) : 0;
            $x = self::LEGEND_COLUMN_X[$column] ?? self::LEGEND_COLUMN_X[0];
            $y = self::LEGEND_START + ($pos % max($lines, 1)) * self::LEGEND_LINE;
            $point_phr = ($point_col_id === null) ? null : ($model->phr_cells[$row_key][$point_col_id] ?? null);
            $name = $html->esc($point_phr?->name() ?? '');
            $row_txt = $html->esc($row_key);
            if ($name == '') {
                $name = $row_txt;
                $row_txt = '';
            } else {
                $row_txt = ' <tspan class="p">' . self::LEGEND_SEP . $row_txt . '</tspan>';
            }
            $result .= '<text x="' . $x . '" y="' . $y . '"><tspan class="n">' . $nbr . '</tspan>  '
                . $name . $row_txt . '</text>' . self::NEWLINE;
        }
        return $result . '</g>' . self::NEWLINE;
    }


    /*
     * leverage plot
     */

    /**
     * one labelled point per table row placed by two numbers of the row, with the quadrants of
     * the big and the small numbers and the lines of an equal ratio of the two numbers, like
     * global-problems-top4_scatter.svg: e.g. the gain of the solution up against the loss of
     * the problem to the right, so that a point above a steep line is a problem with a big lever
     *
     * @param table_model $model the rows and columns of the table
     * @param int|string $y_col_id the id of the value column shown on the vertical axis
     * @param int|string $x_col_id the id of the value column shown on the horizontal axis
     * @param user_message $msg to report a problem while formatting a number
     * @param phrase_list $context_phr_lst the phrases of the page, which name the chart if the
     *                                     table has no phrase column
     * @return string the svg code of the chart
     */
    private function leverage(
        table_model  $model,
        int|string   $y_col_id,
        int|string   $x_col_id,
        user_message $msg,
        phrase_list  $context_phr_lst
    ): string
    {
        $html = new html_base();
        $points = $this->scatter_points($model, $y_col_id, $x_col_id);
        $x_scale = $this->scale(array_merge([0], array_column($points, 3)), self::SCATTER_PLOT_LEFT, self::SCATTER_PLOT_RIGHT);
        $y_scale = $this->scale(array_merge([0], array_column($points, 2)), self::SCATTER_PLOT_BOTTOM, self::SCATTER_PLOT_TOP);
        $y_text = $this->column_text($model, $y_col_id);
        $x_text = $this->column_text($model, $x_col_id);
        // each point is named by its row, e.g. the problem, so the phrases of the page name the chart
        $title = $this->chart_title($context_phr_lst, $y_text . ' ' . msg_id::CHART_VERSUS->text() . ' ' . $x_text);
        $result = $this->svg_start(self::SCATTER_WIDTH, self::LEVERAGE_HEIGHT, self::STYLE_LEVERAGE, $title);
        $result .= $this->heading($html->esc($title), $html->esc(msg_id::CHART_LEVERAGE_TIP->text()));
        $result .= $this->scatter_axes($x_scale, $y_scale, $html->esc($x_text), $html->esc($y_text));
        $result .= $this->quadrants($html->esc($x_text), $html->esc($y_text));
        $result .= $this->ratio_lines($x_scale, $y_scale, $this->ratios($points));
        $ratio_text = $y_text . ' ' . msg_id::CHART_PER->text() . ' ' . $x_text;
        foreach ($points as [$row_key, , $y_nbr, $x_nbr]) {
            $result .= $this->labelled_point($model, $row_key, $this->pos($x_scale, $x_nbr),
                $this->pos($y_scale, $y_nbr), $this->ratio_line_text($ratio_text, $y_nbr, $x_nbr), $msg);
        }
        return $result . '</svg>';
    }

    /**
     * the right half of the plot shaded, the upper quarter for a big number on both axes and the
     * lower one for a big horizontal and a small vertical number, with the four quarters named
     *
     * @param string $x_text the escaped name of the horizontal axis e.g. "loss"
     * @param string $y_text the escaped name of the vertical axis e.g. "gain"
     * @return string the svg code of the quarters
     */
    private function quadrants(string $x_text, string $y_text): string
    {
        $mid_x = (self::SCATTER_PLOT_LEFT + self::SCATTER_PLOT_RIGHT) / 2;
        $mid_y = (self::SCATTER_PLOT_TOP + self::SCATTER_PLOT_BOTTOM) / 2;
        $width = self::SCATTER_PLOT_RIGHT - $mid_x;
        $result = '<rect class="q-high" x="' . $mid_x . '" y="' . self::SCATTER_PLOT_TOP . '" width="' . $width
            . '" height="' . ($mid_y - self::SCATTER_PLOT_TOP) . '"/>' . self::NEWLINE;
        $result .= '<rect class="q-low" x="' . $mid_x . '" y="' . $mid_y . '" width="' . $width
            . '" height="' . (self::SCATTER_PLOT_BOTTOM - $mid_y) . '"/>' . self::NEWLINE;
        $high = msg_id::CHART_QUADRANT_HIGH->text();
        $low = msg_id::CHART_QUADRANT_LOW->text();
        $top = self::SCATTER_PLOT_TOP + self::QUADRANT_TEXT_GAP + self::TEXT_SIZE;
        $bottom = self::SCATTER_PLOT_BOTTOM - self::QUADRANT_TEXT_GAP;
        $right = self::SCATTER_PLOT_RIGHT - self::QUADRANT_TEXT_GAP;
        $left = self::SCATTER_PLOT_LEFT + self::QUADRANT_TEXT_GAP;
        $result .= '<g class="ax">' . self::NEWLINE;
        foreach ([[$right, $top, $high, $high, 'end'], [$right, $bottom, $high, $low, 'end'],
                     [$left, $top, $low, $high, 'start'], [$left, $bottom, $low, $low, 'start']] as [$x, $y, $x_size, $y_size, $anchor]) {
            $result .= $this->text($x, $y, $x_size . ' ' . $x_text . self::LEGEND_SEP . $y_size . ' ' . $y_text,
                'text-anchor="' . $anchor . '"');
        }
        return $result . '</g>' . self::NEWLINE;
    }

    /**
     * the ratios of an equal leverage that the points of the plot span, e.g. 2, 5 and 20 if the
     * gain per loss of the points is between 1.6 and 17
     *
     * @param array $points the points of scatter_points
     * @return array at most RATIO_LINES round ratios of the vertical to the horizontal number
     */
    private function ratios(array $points): array
    {
        $ratios = [];
        foreach ($points as [, , $y_nbr, $x_nbr]) {
            if ($x_nbr > 0 and $y_nbr > 0) {
                $ratios[] = $y_nbr / $x_nbr;
            }
        }
        $result = [];
        if ($ratios != []) {
            $max = max($ratios);
            // the round numbers 1, 2, 5, 10, ... from the smallest to the biggest ratio
            for ($ratio = $this->nice_ceil(min($ratios)); $ratio <= $max; $ratio = $this->nice_ceil($ratio * self::NEXT_NICE_FACTOR)) {
                $result[] = $ratio;
            }
            if ($result == []) {
                $result = [$this->nice_floor($max)];
            }
            $count = count($result);
            if ($count > self::RATIO_LINES) {
                $result = [$result[0], $result[intdiv($count, 2)], $result[$count - 1]];
            }
        }
        return $result;
    }

    /**
     * the dashed lines through the origin on which the ratio of the two numbers is the same
     *
     * @param array $x_scale the scale of the horizontal axis (see scale)
     * @param array $y_scale the scale of the vertical axis
     * @param array $ratios the ratios of the lines (see ratios)
     * @return string the svg code of the lines with their ratio, '' if an axis does not start at zero
     */
    private function ratio_lines(array $x_scale, array $y_scale, array $ratios): string
    {
        $result = '';
        // a line of an equal ratio starts at the origin, so it is drawn only on two linear axes from zero
        $from_zero = (!$x_scale[self::SCALE_LOG] and !$y_scale[self::SCALE_LOG]
            and $x_scale[self::SCALE_LOW] == 0 and $y_scale[self::SCALE_LOW] == 0);
        if ($from_zero) {
            foreach ($ratios as $ratio) {
                $x_end = min($x_scale[self::SCALE_HIGH], $y_scale[self::SCALE_HIGH] / $ratio);
                $x = $this->pos($x_scale, $x_end);
                $y = $this->pos($y_scale, $ratio * $x_end);
                $result .= '<line class="ratio" x1="' . $this->pos($x_scale, 0) . '" y1="' . $this->pos($y_scale, 0)
                    . '" x2="' . $x . '" y2="' . $y . '"/>' . self::NEWLINE;
                $result .= $this->text($x + self::BASELINE_SHIFT, $y - self::BASELINE_SHIFT,
                    self::RATIO_PREFIX . $this->tick_text($ratio), 'class="ratio-txt"');
            }
        }
        return $result;
    }

    /**
     * @param string $ratio_text the name of the ratio e.g. "gain per loss"
     * @param float $y_nbr the number of the vertical axis
     * @param float $x_nbr the number of the horizontal axis
     * @return string the tooltip line of the ratio e.g. "gain per loss: 16" or '' if the horizontal number is zero
     */
    private function ratio_line_text(string $ratio_text, float $y_nbr, float $x_nbr): string
    {
        $result = '';
        if ($x_nbr != 0) {
            $result = $ratio_text . self::LABEL_SEP . $this->tick_text(round($y_nbr / $x_nbr, 1));
        }
        return $result;
    }

    /**
     * one point of the leverage plot named by its row, the name left of a point in the right
     * half, so that it stays inside the plot, with the tooltip of the whole row and its ratio
     *
     * @param table_model $model the rows and columns of the table
     * @param string $row_key the key of the row, which names the point
     * @param float $x the horizontal position of the point in pixel
     * @param float $y the vertical position of the point in pixel
     * @param string $ratio_line the tooltip line of the ratio of the point (see ratio_line_text)
     * @param user_message $msg to report a problem while formatting a number
     * @return string the svg code of the point
     */
    private function labelled_point(
        table_model  $model,
        string       $row_key,
        float        $x,
        float        $y,
        string       $ratio_line,
        user_message $msg
    ): string
    {
        $html = new html_base();
        $tooltip = $this->tooltip($model, $row_key, $msg);
        if ($ratio_line != '') {
            $tooltip .= self::NEWLINE . $html->esc($ratio_line);
        }
        $in_right_half = ($x > (self::SCATTER_PLOT_LEFT + self::SCATTER_PLOT_RIGHT) / 2);
        $label_x = $in_right_half ? $x - self::LEVERAGE_LABEL_GAP : $x + self::LEVERAGE_LABEL_GAP;
        $anchor = $in_right_half ? 'end' : 'start';
        $result = '<g class="pt"><title>' . $tooltip . '</title>';
        $result .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . self::LEVERAGE_POINT_RADIUS . '"/>';
        $result .= '<text class="lbl" x="' . $label_x . '" y="' . $y . '" text-anchor="' . $anchor . '">'
            . $html->esc($row_key) . '</text></g>' . self::NEWLINE;
        return $result;
    }


    /*
     * scale
     */

    /**
     * the scale of an axis that covers the given numbers with ticks at round numbers
     *
     * a log scale is used if the numbers differ by more than LOG_RATIO, so that the small
     * numbers stay visible beside the big ones; a linear scale starts at zero unless a number
     * is negative and leaves HEADROOM above the biggest number
     *
     * @param array $numbers the numbers that the axis has to cover
     * @param float $px_start the pixel position of the smallest number
     * @param float $px_end the pixel position of the biggest number
     * @return array the scale keyed by the SCALE_* consts
     */
    private function scale(array $numbers, float $px_start, float $px_end): array
    {
        // a plot without any number still has an axis
        if ($numbers == []) {
            $numbers = [0];
        }
        $min = min($numbers);
        $max = max($numbers);
        $log = ($min > 0 and $max / $min >= self::LOG_RATIO);
        $ticks = [];
        if ($log) {
            $low = $this->nice_floor($min);
            $high = $this->nice_ceil($max);
            for ($exp = (int)ceil(log10($low)); $exp <= floor(log10($high)); $exp++) {
                $ticks[] = 10 ** $exp;
            }
        } else {
            $low = ($min >= 0) ? 0 : -$this->nice_ceil(-$min);
            $high = ($max <= 0) ? 0 : $max;
            if ($high > 0) {
                // the ticks are round numbers and the biggest number stays below the top tick
                $step = $this->nice_floor($high / self::TICKS);
                $high = ceil($high * self::HEADROOM / $step) * $step;
            }
            if ($high <= $low) {
                $high = $low + 1;
            }
            $step = $this->nice_floor(($high - $low) / self::TICKS);
            for ($tick = $low; $tick <= $high + $step / 1000; $tick += $step) {
                $ticks[] = round($tick, 10);
            }
        }
        return [self::SCALE_LOG => $log, self::SCALE_LOW => $low, self::SCALE_HIGH => $high,
            self::SCALE_TICKS => $ticks, self::SCALE_START => $px_start, self::SCALE_END => $px_end];
    }

    /**
     * @param array $scale the scale of the axis (see scale)
     * @param float $number a number within the scale
     * @return float the pixel position of the number
     */
    private function pos(array $scale, float $number): float
    {
        $low = $scale[self::SCALE_LOW];
        $high = $scale[self::SCALE_HIGH];
        if ($scale[self::SCALE_LOG]) {
            $share = (log10($number) - log10($low)) / (log10($high) - log10($low));
        } else {
            $share = ($number - $low) / ($high - $low);
        }
        return round($scale[self::SCALE_START] + $share * ($scale[self::SCALE_END] - $scale[self::SCALE_START]), 1);
    }

    /**
     * @param float $number a positive number
     * @return float the biggest 1, 2 or 5 times a power of ten that is not above the number
     */
    private function nice_floor(float $number): float
    {
        $exp = floor(log10($number));
        $mantissa = $number / 10 ** $exp;
        $step = ($mantissa >= 5) ? 5 : (($mantissa >= 2) ? 2 : 1);
        return $step * 10 ** $exp;
    }

    /**
     * @param float $number a positive number
     * @return float the smallest 1, 2 or 5 times a power of ten that is not below the number
     */
    private function nice_ceil(float $number): float
    {
        $exp = floor(log10($number));
        $mantissa = $number / 10 ** $exp;
        $step = ($mantissa <= 1) ? 1 : (($mantissa <= 2) ? 2 : (($mantissa <= 5) ? 5 : 10));
        return $step * 10 ** $exp;
    }

    /**
     * @param float $tick a tick number of an axis
     * @return string the tick without trailing zeros e.g. "0.1" or "10"
     */
    private function tick_text(float $tick): string
    {
        return rtrim(rtrim(number_format($tick, 6, '.', ''), '0'), '.');
    }


    /*
     * texts
     */

    /**
     * the tooltip of a row: the phrases of the row and per value column its number with the
     * unit and the range, e.g. "health" plus "loss: 5.5a trillion EUR (2.2 – 13.75)"
     *
     * @param table_model $model the rows and columns of the table
     * @param string $row_key the key of the row
     * @param user_message $msg to report a problem while formatting a number
     * @return string the escaped tooltip text, one line per column
     */
    private function tooltip(table_model $model, string $row_key, user_message $msg): string
    {
        $html = new html_base();
        $lines = [$row_key];
        $phr_txt = $this->phrase_cell_text($model, $row_key);
        if ($phr_txt != '') {
            $lines = [$phr_txt . ' (' . $row_key . ')'];
        }
        foreach ($model->value_col_ids() as $col_id) {
            $cell_txt = $this->cell_text($model, $row_key, $col_id, $msg);
            if ($cell_txt != '') {
                $lines[] = $this->column_text($model, $col_id) . self::LABEL_SEP . $cell_txt;
            }
        }
        return $html->esc(implode(self::NEWLINE, $lines));
    }

    /**
     * the numbers of one cell as text like the table shows them, e.g. "5.5a trillion EUR (2.2 – 13.75)"
     *
     * @param table_model $model the rows and columns of the table
     * @param string $row_key the key of the row
     * @param int|string $col_id the id of the value column
     * @param user_message $msg to report a problem while formatting a number
     * @return string the text of the cell or '' if the cell has no number
     */
    private function cell_text(table_model $model, string $row_key, int|string $col_id, user_message $msg): string
    {
        [$centre, $low, $high,] = $model->cell_numbers($row_key, $col_id);
        $result = '';
        if ($centre != null) {
            $result = $centre->value($msg);
            // an assumed number carries its mark like in the table (see sandbox_value::quality_mark)
            if (in_array(words::ASSUMED, $centre->grp->phr_lst()->names())) {
                $result .= sandbox_value::QUALITY_MARK_ASSUMED;
            }
            $unit = $this->unit_text($model, $col_id);
            if ($unit != '') {
                $result .= ' ' . $unit;
            }
            if ($low != null or $high != null) {
                $result .= value_list::RANGE_START . ($low?->value($msg) ?? '')
                    . value_list::RANGE_SEP . ($high?->value($msg) ?? '') . value_list::RANGE_END;
            }
        }
        return $result;
    }

    /**
     * @param table_model $model the rows and columns of the table
     * @param string $row_key the key of the row
     * @return string the names of the phrases that the phrase columns show for the row e.g. the solution
     */
    private function phrase_cell_text(table_model $model, string $row_key): string
    {
        $names = [];
        foreach ($model->phrase_col_ids() as $col_id) {
            $phr = $model->phr_cells[$row_key][$col_id] ?? null;
            if ($phr != null) {
                $names[] = $phr->name();
            }
        }
        return implode(', ', $names);
    }

    /**
     * @param table_model $model the rows and columns of the table
     * @param int|string $col_id the id of a value column
     * @return string the name that heads the column like in the table e.g. "loss"
     */
    private function column_text(table_model $model, int|string $col_id): string
    {
        return $model->col_head[$col_id]->name();
    }

    /**
     * @param table_model $model the rows and columns of the table
     * @param int|string $col_id the id of a value column
     * @return string the unit of the column e.g. "trillion EUR" or '' if the column has no common unit
     */
    private function unit_text(table_model $model, int|string $col_id): string
    {
        $names = [];
        foreach (($model->col_unit[$col_id] ?? new phrase_list())->lst() as $phr) {
            $names[] = $phr->name();
        }
        return implode(' ', $names);
    }

    /**
     * the phrases that name the points of the scatter plot: the phrase column of the numbers
     * plotted up (see point_column), e.g. "solution" for the gain of the solutions and not the
     * "reason" column before it, else the phrases of the page
     *
     * @param table_model $model the rows and columns of the table
     * @param phrase_list $context_phr_lst the phrases of the page
     * @param int|string|null $point_col_id the id of the phrase column that names the points or null if none
     * @return phrase_list the phrases that name the chart
     */
    private function legend_phrases(table_model $model, phrase_list $context_phr_lst, int|string|null $point_col_id): phrase_list
    {
        $result = $context_phr_lst;
        if ($point_col_id !== null) {
            $result = new phrase_list();
            $result->add_phrase($model->phr_col[$point_col_id]);
        }
        return $result;
    }

    /**
     * the phrase column whose phrase the numbers of a value column carry, e.g. the "solution"
     * column for the potential gain, because a gain value names the solution, while no gain
     * value names the reason of the problem
     *
     * @param table_model $model the rows and columns of the table
     * @param int|string $col_id the id of the plotted value column
     * @return int|string|null the id of the first such phrase column or null if the numbers carry none
     */
    private function point_column(table_model $model, int|string $col_id): int|string|null
    {
        $result = null;
        foreach ($model->phrase_col_ids() as $phr_col_id) {
            foreach ($model->shown_keys as $row_key) {
                $phr = $model->phr_cells[$row_key][$phr_col_id] ?? null;
                [$centre, , ,] = $model->cell_numbers($row_key, $col_id);
                if ($result === null and $phr != null
                    and in_array($phr->name(), $centre?->grp->phr_lst()->names() ?? [])) {
                    $result = $phr_col_id;
                }
            }
        }
        return $result;
    }

    /**
     * @param phrase_list $phr_lst the phrases that name the chart e.g. "global problem"
     * @param string $what the plotted columns e.g. "potential loss in trillion EUR"
     * @return string the title of the chart e.g. "global problems: potential loss in trillion EUR"
     */
    private function chart_title(phrase_list $phr_lst, string $what): string
    {
        $names = [];
        foreach ($phr_lst->lst() as $phr) {
            $names[] = $phr->obj()->plural_name();
        }
        $result = implode(', ', $names);
        if ($result != '') {
            $result .= self::TITLE_SEP;
        }
        return $result . $what;
    }


    /*
     * svg elements
     */

    /**
     * @param int $width the width of the chart in pixel
     * @param int $height the height of the chart in pixel
     * @param string $style the css of the chart type
     * @param string $label the chart title for a screen reader
     * @return string the opening svg tag with the style and the background
     */
    private function svg_start(float $width, float $height, string $style, string $label): string
    {
        $result = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height
            . '" width="' . $width . '" height="' . $height . '" role="img" aria-label="'
            . htmlspecialchars($label, ENT_QUOTES) . '">' . self::NEWLINE;
        $result .= '<style>' . $this->style($style) . self::NEWLINE . '</style>' . self::NEWLINE;
        return $result . '<rect class="bg" width="' . $width . '" height="' . $height . '"/>' . self::NEWLINE;
    }

    /**
     * @param string $style the css of the chart type
     * @return string the css of the chart: the colours of both modes, the shared css and the css of the type
     */
    private function style(string $style): string
    {
        return self::NEWLINE . '        :root { ' . $this->color_css(self::COLORS_LIGHT) . ' }'
            . self::NEWLINE . '        @media (prefers-color-scheme: dark) {'
            . self::NEWLINE . '        :root { ' . $this->color_css(self::COLORS_DARK) . ' }'
            . self::NEWLINE . '        }' . self::STYLE_SHARED . $style;
    }

    /**
     * @param array $colors the colours keyed by the css variable name
     * @return string the css variable definitions e.g. "--bg: #fbfaf7; --fg: #1d1d1b;"
     */
    private function color_css(array $colors): string
    {
        $result = [];
        foreach ($colors as $name => $color) {
            $result[] = '--' . $name . ': ' . $color . ';';
        }
        return implode(' ', $result);
    }

    /**
     * @param string $title the escaped title line
     * @param string $subtitle the escaped line below the title
     * @return string the svg code of the two lines
     */
    private function heading(string $title, string $subtitle): string
    {
        return '<text class="h" x="' . self::TEXT_X . '" y="' . self::TITLE_Y . '">' . $title . '</text>' . self::NEWLINE
            . '<text class="sub" x="' . self::TEXT_X . '" y="' . self::SUBTITLE_Y . '">' . $subtitle . '</text>' . self::NEWLINE;
    }

    /**
     * @return string the svg code of a line between the two points
     */
    private function line(float $x1, float $y1, float $x2, float $y2): string
    {
        return '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '"/>' . self::NEWLINE;
    }

    /**
     * @param string $text the escaped text
     * @param string $attr further attributes of the text element e.g. the anchor
     * @return string the svg code of a text at the given position
     */
    private function text(float $x, float $y, string $text, string $attr = ''): string
    {
        $attr = ($attr == '') ? '' : ' ' . $attr;
        return '<text x="' . $x . '" y="' . $y . '"' . $attr . '>' . $text . '</text>' . self::NEWLINE;
    }

}
