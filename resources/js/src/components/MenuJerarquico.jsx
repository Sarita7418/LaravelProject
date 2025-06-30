import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from '../axios'

const MenuJerarquico = () => {
    const [menuItems, setMenuItems] = useState([])
    const [abiertos, setAbiertos] = useState({})
    const navigate = useNavigate()


    useEffect(() => {
        axios.get('/api/menu-items')
            .then(res => {
                console.log('✅ Menú recibido:', res.data) // 👈 ahora sí existe `res`
                setMenuItems(res.data)
            })
            .catch(err => console.error('❌ Error cargando menú:', err))
    }, [])


    const toggleSubmenu = (id) => {
        setAbiertos(prev => ({
            ...prev,
            [id]: !prev[id]
        }))
    }

    const renderMenu = (items) => (
        <ul className="submenu">
            {items.map(item => (
                <li key={item.id}>
                    <div
                        className="menu-boton"
                        onClick={() => {
                            if (item.hijos && item.hijos.length > 0) {
                                toggleSubmenu(item.id)
                            } else if (item.url?.ruta) {
                                navigate(item.url.ruta) // 👈 navegación real
                            }
                        }}
                    >
                        {item.hijos && item.hijos.length > 0 && (
                            <span>{abiertos[item.id] ? '▼' : '▶'}</span>
                        )}
                        <span>{item.item}</span>
                    </div>
                    {item.hijos && item.hijos.length > 0 && abiertos[item.id] && renderMenu(item.hijos)}
                </li>
            ))}
        </ul>
    )

    return (
        <div className="sidebar-menu">
            {renderMenu(menuItems)}
        </div>
    )
}

export default MenuJerarquico
