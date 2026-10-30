/*

    test/php/utils/math_function_report.js - calculate math expressions with the javascript evaluators
    --------------------------------------

    started by math_function_report.php: reads the expressions as json from stdin and writes the
    result per evaluator as json to stdout; the functions and consts are cut out of the real
    scripts, so that the report shows what a browser calculates and never a copy of the code

*/

const fs = require('fs');
const path = require('path');

const NO_NUMBER = 'no number';
const FATAL = 'fatal: ';

const CALC_CONSTS = ['TOKEN_NUM', 'TOKEN_OP', 'TOKEN_REF', 'FUNC_MIN', 'FUNC_MAX', 'FUNC_IF', 'FUNC_ARG_SEPARATOR', 'POWER', 'ONE_NUMBER', 'LIST_NUMBER'];
const JSON_CONSTS = ['TOKEN_PATTERN', 'FUNC_MIN', 'FUNC_MAX', 'FUNC_IF', 'FUNC_ARG_SEPARATOR', 'POWER', 'ONE_NUMBER', 'LIST_NUMBER'];
const EDITOR_CONSTS = ['FUNC_MIN', 'FUNC_MAX', 'FUNC_IF', 'FUNC_ARG_SEPARATOR', 'ONE_NUMBER', 'LIST_NUMBER', 'POWER', 'T_PHR', 'T_NUM', 'T_OP', 'T_BR'];
// the texts of the formula editor are not needed, because only the number is compared
const EDITOR_STUBS = 'const TXT = {}; const VAR = {}; function txt() { return ""; }';

/* the function with this name up to its closing brace */
function functionCode(source, name) {
    const start = source.indexOf('function ' + name + '(');
    if (start < 0) {
        throw new Error('function ' + name + ' is missing');
    }
    let depth = 0;
    let pos = source.indexOf('{', start);
    do {
        if (source[pos] === '{') {
            depth++;
        } else if (source[pos] === '}') {
            depth--;
        }
        pos++;
    } while (depth > 0 && pos < source.length);
    return source.slice(start, pos);
}

/* the const with this name, which is written in one line */
function constCode(source, name) {
    const found = source.match(new RegExp('const ' + name + ' = .*;'));
    if (found === null) {
        throw new Error('const ' + name + ' is missing');
    }
    return found[0];
}

/* the evaluator made of the real code of the script */
function loadEvaluator(file, consts, functions, stubs, call) {
    const source = fs.readFileSync(path.join(input.dir, file), 'utf8');
    const code = stubs
        + consts.map(name => constCode(source, name)).join('\n')
        + functions.map(name => functionCode(source, name)).join('\n')
        + '\nreturn ' + call + ';';
    return new Function(code)();
}

function numberText(num) {
    return num === null || !Number.isFinite(num) ? NO_NUMBER : JSON.stringify(num);
}

/* the result of one evaluator for one expression, so a crash stays visible as a result */
function run(evaluator, testCase) {
    let result;
    try {
        result = numberText(evaluator(testCase));
    } catch (e) {
        result = FATAL + e.name;
    }
    return result;
}

const input = JSON.parse(fs.readFileSync(0, 'utf8'));
const calculator = loadEvaluator('calculator.js', CALC_CONSTS, ['evaluate'], '',
    '(c) => c.tokens === null ? null : evaluate(c.tokens, () => null)');
const calculatorJson = loadEvaluator('calculator_json.js', JSON_CONSTS,
    ['evaluate', 'sum', 'product', 'powerOf', 'finiteNumber', 'compareCondition', 'andCondition', 'orCondition',
        'expectToken', 'ifFunction', 'pickArgument', 'listArguments', 'signed'], '',
    '(c) => evaluate(c.txt)');
const formulaEditor = loadEvaluator('formula_editor.js', EDITOR_CONSTS, ['tokenize', 'evaluate'], EDITOR_STUBS,
    '(c) => { const t = tokenize(c.txt); return t.ok ? evaluate(t.tokens, () => 1, () => [1]) : null; }');

const output = input.cases.map(testCase => ({
    calculator: run(calculator, testCase),
    calculator_json: run(calculatorJson, testCase),
    formula_editor: run(formulaEditor, testCase),
}));
process.stdout.write(JSON.stringify(output));
