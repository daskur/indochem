document.addEventListener('DOMContentLoaded', () => {
    const formatResult = (value, decimals = 6) => {
        if (!Number.isFinite(value)) return 'Enter valid values';
        if (Math.abs(value) >= 100000 || (Math.abs(value) < 0.000001 && value !== 0)) {
            return value.toExponential(6);
        }
        return Number(value.toFixed(decimals)).toString();
    };

    document.querySelectorAll('[data-calculator]').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            const result = form.querySelector('[data-result]');
            const type = form.dataset.calculator;

            try {
                const number = id => Number(form.elements[id].value);
                let value;

                if (type === 'molarity') {
                    const moles = number('moles');
                    const volume = number('volume');
                    if (volume <= 0) throw new Error();
                    value = `${formatResult(moles / volume)} mol/L`;
                } else if (type === 'normality') {
                    const molarity = number('molarity');
                    const equivalents = number('equivalents');
                    value = `${formatResult(molarity * equivalents)} eq/L`;
                } else if (type === 'molar-mass') {
                    const weights = {
                        H:1.008,He:4.003,Li:6.94,Be:9.012,B:10.81,C:12.011,N:14.007,O:15.999,F:18.998,Ne:20.180,
                        Na:22.990,Mg:24.305,Al:26.982,Si:28.085,P:30.974,S:32.06,Cl:35.45,K:39.098,Ca:40.078,
                        Sc:44.956,Ti:47.867,V:50.942,Cr:51.996,Mn:54.938,Fe:55.845,Co:58.933,Ni:58.693,Cu:63.546,
                        Zn:65.38,Ag:107.868,Sn:118.710,I:126.904,Ba:137.327,Au:196.967,Pb:207.2
                    };
                    const formula = form.elements.formula.value.trim();
                    if (!formula) throw new Error();

                    const calculateGroup = (text, start) => {
                        let index = start;
                        let total = 0;
                        while (index < text.length && text[index] !== ')') {
                            if (text[index] === '(') {
                                const inner = calculateGroup(text, index + 1);
                                index = inner.next;
                                let multiplierText = '';
                                while (/\d/.test(text[index] || '')) multiplierText += text[index++];
                                total += inner.total * (multiplierText ? Number(multiplierText) : 1);
                            } else {
                                let symbol = text[index++];
                                if (/[a-z]/.test(text[index] || '')) symbol += text[index++];
                                if (!weights[symbol]) throw new Error();
                                let multiplierText = '';
                                while (/\d/.test(text[index] || '')) multiplierText += text[index++];
                                total += weights[symbol] * (multiplierText ? Number(multiplierText) : 1);
                            }
                        }
                        return { total, next: index < text.length ? index + 1 : index };
                    };

                    value = `${formatResult(calculateGroup(formula, 0).total)} g/mol`;
                } else if (type === 'dilution') {
                    const c1 = number('c1');
                    const c2 = number('c2');
                    const v2 = number('v2');
                    if (c1 <= 0 || c2 <= 0 || v2 <= 0) throw new Error();
                    value = `${formatResult((c2 * v2) / c1)} ${form.elements.dilution_unit.value}`;
                } else if (type === 'ph') {
                    const hydrogen = number('hydrogen');
                    if (hydrogen <= 0) throw new Error();
                    value = `pH ${formatResult(-Math.log10(hydrogen), 4)}`;
                } else if (type === 'yield') {
                    const actual = number('actual');
                    const theoretical = number('theoretical');
                    if (theoretical <= 0) throw new Error();
                    value = `${formatResult((actual / theoretical) * 100, 4)}%`;
                } else if (type === 'density') {
                    const mass = number('mass');
                    const volume = number('volume');
                    if (volume <= 0) throw new Error();
                    value = `${formatResult(mass / volume)} ${form.elements.density_unit.value}`;
                } else if (type === 'unit-converter') {
                    const valueIn = number('value');
                    const from = form.elements.from.value;
                    const to = form.elements.to.value;
                    const factors = {
                        mg:0.001,g:1,kg:1000,
                        mL:1,L:1000,
                        mm:0.001,m:1,km:1000
                    };
                    if (!(from in factors) || !(to in factors)) throw new Error();
                    const groups = { mass:['mg','g','kg'], volume:['mL','L'], length:['mm','m','km'] };
                    const sourceGroup = Object.values(groups).find(group => group.includes(from));
                    if (!sourceGroup || !sourceGroup.includes(to)) throw new Error();
                    value = `${formatResult(valueIn * factors[from] / factors[to])} ${to}`;
                }

                result.textContent = value;
                result.classList.add('has-result');
            } catch (error) {
                result.textContent = 'Enter valid positive values and check the formula.';
                result.classList.add('has-result');
            }
        });
    });
});
